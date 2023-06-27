<?php

namespace App\Http\Controllers\API\Registries;

use App\Helpers\Fiscal;
use App\Helpers\PriceConverter;
use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\PaymentStatus;
use App\Models\Hit\Hit;
use App\Models\Payments\Payment;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class TransactionsRegistryController extends ApiController
{
    protected array $defaultFilters = [
        'date_from' => null,
        'date_to' => null,
        'terminal_id' => null,
        'select' => null,
    ];

    protected array $rememberFilters = [
        'date_from', 'date_to', 'terminal_id', 'select',
    ];

    protected string $rememberKey = CookieKeys::transactions_registry_list;

    /**
     * Initialize default filters.
     */
    public function __construct()
    {
        $this->defaultFilters['date_from'] = Carbon::now()->startOfDay()->format('Y-m-d\TH:i');
        $this->defaultFilters['date_to'] = Carbon::now()->endOfDay()->format('Y-m-d\TH:i');
    }

    /**
     * Get transactions list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        $current = Currents::get($request);
        Hit::register($current->isStaffTerminal() ? HitSource::terminal : HitSource::admin);
        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey);

        $dateFrom = Carbon::parse($filters['date_from'])->seconds(0)->microseconds(0);
        $filters['date_from'] = $dateFrom->format('Y-m-d\TH:i');

        $dateTo = Carbon::parse($filters['date_to'])->seconds(59)->microseconds(999);
        $filters['date_to'] = $dateTo->format('Y-m-d\TH:i');

        $terminalId = $this->getTerminalId($request);
        $excursionId = $filters['excursion_id'] ?? null;

        $query = $this->getListQuery($request->search(), $filters, $terminalId, $excursionId);

        $queryBackup = $query->clone();
        $payments = $query->paginate($request->perPage(10, $this->rememberKey));

        /** @var LengthAwarePaginator $payments */
        $payments->transform(function (Payment $payment) {
            return [
                'id' => $payment->id,
                'gate' => $payment->gate,
                'external_id' => $payment->external_id,
                'date' => $payment->created_at->format('d.m.Y'),
                'time' => $payment->created_at->format('H:i'),
                'order_id' => $payment->order_id,
                'order_external_id' => $payment->order->external_id ?? null,
                'status' => $payment->status->name,
                'status_id' => $payment->status_id,
                'fiscal' => $payment->fiscal,
                'total' => $payment->total,
                'by_card' => $payment->by_card,
                'by_cash' => $payment->by_cash,
                'terminal' => $payment->terminal->name ?? null,
                'terminal_id' => $payment->terminal_id,
                'position' => $payment->position ? $payment->position->user->profile->compactName : null,
                'position_id' => $payment->position->user_id ?? null,
            ];
        });

        if (empty($excursionId)) {
            $saleQuery = $queryBackup->clone()->where('status_id', PaymentStatus::sale);
            $returnQuery = $queryBackup->clone()->where('status_id', PaymentStatus::return);

            $saleTotal = PriceConverter::storeToPrice($saleQuery->sum('total'));
            $saleByCard = PriceConverter::storeToPrice($saleQuery->sum('by_card'));
            $saleByCash = PriceConverter::storeToPrice($saleQuery->sum('by_cash'));

            $returnTotal = PriceConverter::storeToPrice($returnQuery->sum('total'));
            $returnByCard = PriceConverter::storeToPrice($returnQuery->sum('by_card'));
            $returnByCash = PriceConverter::storeToPrice($returnQuery->sum('by_cash'));

            $overallTotal = $saleTotal - $returnTotal;
        } else {
            $ticketsByCash = Ticket::query()
                ->whereHas('trip', function (Builder $query) use ($excursionId) {
                    $query->where('excursion_id', $excursionId);
                })
                ->whereHas('order.payments', function (Builder $query) use ($excursionId, $filters) {
                    $query->where('by_cash', '>', 0)
                        ->where('status_id', PaymentStatus::sale)
                        ->where('gate', 'lifepos')
                        ->where('created_at', '>=', $filters['date_from'])
                        ->where('created_at', '<=', $filters['date_to']);
                })->sum('base_price');
            $ticketsByCash = PriceConverter::storeToPrice($ticketsByCash);

            $ticketsByCard = Ticket::query()
                ->whereHas('trip', function (Builder $query) use ($excursionId) {
                    $query->where('excursion_id', $excursionId);
                })
                ->whereHas('order.payments', function (Builder $query) use ($excursionId, $filters) {
                    $query->where('by_card', '>', 0)
                        ->where('status_id', PaymentStatus::sale)
                        ->where('gate', 'lifepos')
                        ->where('created_at', '>=', $filters['date_from'])
                        ->where('created_at', '<=', $filters['date_to']);
                })->sum('base_price');
            $ticketsByCard = PriceConverter::storeToPrice($ticketsByCard);

            $returnTicketsByCash = Ticket::query()
                ->whereHas('trip', function (Builder $query) use ($excursionId) {
                    $query->where('excursion_id', $excursionId);
                })
                ->whereHas('order.payments', function (Builder $query) use ($excursionId, $filters) {
                    $query->where('by_cash', '>', 0)
                        ->where('status_id', PaymentStatus::return)
                        ->where('gate', 'lifepos')
                        ->where('created_at', '>=', $filters['date_from'])
                        ->where('created_at', '<=', $filters['date_to']);
                })->sum('base_price');
            $returnTicketsByCash = PriceConverter::storeToPrice($returnTicketsByCash);

            $returnTicketsByCard = Ticket::query()
                ->whereHas('trip', function (Builder $query) use ($excursionId) {
                    $query->where('excursion_id', $excursionId);
                })
                ->whereHas('order.payments', function (Builder $query) use ($excursionId, $filters) {
                    $query->where('by_card', '>', 0)
                        ->where('status_id', PaymentStatus::return)
                        ->where('gate', 'lifepos')
                        ->where('created_at', '>=', $filters['date_from'])
                        ->where('created_at', '<=', $filters['date_to']);
                })->sum('base_price');
            $returnTicketsByCard = PriceConverter::storeToPrice($returnTicketsByCard);

            $saleTotal = $ticketsByCash + $ticketsByCard;
            $saleByCard = $ticketsByCard;
            $saleByCash = $ticketsByCash;

            $returnTotal = $returnTicketsByCard + $returnTicketsByCash;
            $returnByCard = $returnTicketsByCard;
            $returnByCash = $returnTicketsByCash;

            $overallTotal = $saleTotal - $returnTotal;
        }

        return APIResponse::list(
            $payments,
            ['Дата и время', '№ заказа', 'Тип, чек', 'Сумма', 'Касса, кассир', 'Идентификаторы'],
            $filters,
            $this->defaultFilters,
            [
                'terminal' => $terminalId,
                'date_from' => $dateFrom->format('d.m.Y, H:i'),
                'date_to' => $dateTo->format('d.m.Y, H:i'),
                'sale_total' => $saleTotal,
                'sale_by_card' => $saleByCard,
                'sale_by_cash' => $saleByCash,
                'return_total' => -$returnTotal,
                'return_by_card' => -$returnByCard,
                'return_by_cash' => -$returnByCash,
                'cash_total' => $saleByCash - $returnByCash,
                'card_total' => $saleByCard - $returnByCard,
                'overall_total' => $overallTotal,
            ]
        )->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }

    public function export(ApiListRequest $request): JsonResponse
    {
        $current = Currents::get($request);
        Hit::register($current->isStaffTerminal() ? HitSource::terminal : HitSource::admin);

        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey);

        $dateFrom = Carbon::parse($filters['date_from'])->seconds(0)->microseconds(0);
        $filters['date_from'] = $dateFrom->format('Y-m-d\TH:i');

        $dateTo = Carbon::parse($filters['date_to'])->seconds(59)->microseconds(999);
        $filters['date_to'] = $dateTo->format('Y-m-d\TH:i');

        $terminalId = $this->getTerminalId($request);
        $excursionId = $request->input('excursion_id');

        $payments = $this->getListQuery($request->search(), $filters, $terminalId, $excursionId)->get();

        $titles = [
            'Дата и время',
            '№ заказа',
            'Тип',
            'Сумма',
            'Касса',
            'Кассир',
        ];

        $payments->transform(function (Payment $payment) {
            return [
                'date' => $payment->created_at->format('d.m.Y H:i'),
                'order_id' => $payment->order_id,
                'status' => $payment->status->name,
                'total' => $payment->total,
                'terminal' => $payment->terminal->name ?? null,
                'position' => $payment->position ? $payment->position->user->profile->compactName : null,
            ];
        });

        $spreadsheet = new Spreadsheet();

        $spreadsheet->setActiveSheetIndex(0)->setTitle('Транзакции')->setShowRowColHeaders(true);

        $spreadsheet->getActiveSheet()->fromArray($titles, '—', 'A1');
        $spreadsheet->getActiveSheet()->fromArray($payments->toArray(), '—', 'A2');
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'] as $col) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        ob_start();
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        $export = ob_get_clean();

        return APIResponse::response([
            'file' => base64_encode($export),
            'file_name' => 'Транзакции ' . Carbon::now()->format('Y-m-d H:i'),
            'type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Build payments list query.
     *
     * @param array $search
     * @param array $filters
     * @param int|null $terminalId
     *
     * @return Builder
     */
    protected function getListQuery(array $search, array $filters, ?int $terminalId, ?int $excursionId): Builder
    {
        $query = Payment::query()
            ->where('gate', 'lifepos')
            ->with(['status', 'order.tickets.trip', 'terminal', 'position', 'position.user', 'position.user.profile']);

        if ($terminalId !== null) {
            $query->where('terminal_id', $terminalId);
        }

        if (!empty($filters['select'])) {
            switch ($filters['select']) {
                case 'no-order':
                    $query->whereNull('order_id');
                    break;
                case 'no-terminal':
                    $query->whereNull('terminal_id');
                    break;
                case 'no-cashier':
                    $query->whereNull('position_id');
                    break;
                case 'no-fiscal':
                    $query->whereNull('fiscal');
                    break;
            }
        }

        // apply search
        if ($search) {
            foreach ($search as $term) {
                $query->where(function (Builder $query) use ($term) {
                    $query
                        ->where('external_id', 'LIKE', "$term%")
                        ->orWhereHas('order', function (Builder $query) use ($term) {
                            $query
                                ->where('id', $term)
                                ->orWhere('external_id', 'LIKE', "$term%");
                        });
                    if (is_numeric($term)) {
                        $value = PriceConverter::priceToStore((float)$term);
                        $query
                            ->orWhere('total', $value)
                            ->orWhere('by_card', $value)
                            ->orWhere('by_cash', $value);
                    }
                });
            }
        } else {
            // apply filters
            if (!empty($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to']);
            }
        }

        $query->when(($excursionId || !empty($filters['excursion_id'])), function (Builder $query) use ($excursionId, $filters) {
            $query->whereHas('order.tickets.trip', function (Builder $query) use ($excursionId, $filters) {
                $query->where('excursion_id', $excursionId ?? $filters['excursion_id']);
            });
        });

        return $query;
    }

    /**
     * Get terminalId for current user.
     *
     * @param APIListRequest $request
     *
     * @return  int|null
     */
    public function getTerminalId(APIListRequest $request): ?int
    {
        $filters = $request->input('filters');
        $current = Currents::get($request);

        if ($current->isStaffAdmin() || $current->isStaffAccountant()) {
            if ($request->has('terminal_id') && $request->input('terminal_id') !== null) {
                return $request->input('terminal_id');
            } else if (!empty($filters['terminal_id'])) {
                return $filters['terminal_id'];
            }
        } else if ($current->isStaffTerminal()) {
            return $current->terminalId();
        }

        return null;
    }

    /**
     * Get transactions list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function fiscal(ApiListRequest $request): JsonResponse
    {
        $gate = $request->input('gate');
        $fiscal = $request->input('id');

        try {
            $fiscal = Fiscal::get($gate, $fiscal);
        } catch (FileNotFoundException $e) {
            return APIResponse::error('Информация о чеке не найдена.');
        }

        return APIResponse::response([
            'fiscal' => $fiscal,
        ]);
    }
}
