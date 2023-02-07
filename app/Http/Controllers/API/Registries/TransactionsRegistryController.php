<?php

namespace App\Http\Controllers\API\Registries;

use App\Helpers\PriceConverter;
use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\PaymentStatus;
use App\Models\Payments\Payment;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
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

    public ?int $terminalId;

    /**
     * Get transactions list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        $this->defaultFilters['date_from'] = Carbon::now()->startOfDay()->format('Y-m-d\TH:i');
        $this->defaultFilters['date_to'] = Carbon::now()->endOfDay()->format('Y-m-d\TH:i');
        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey);

        $dateFrom = Carbon::parse($filters['date_from'])->seconds(0)->microseconds(0);
        $dateTo = Carbon::parse($filters['date_to'])->seconds(59)->microseconds(999);

        $filters['date_from'] = $dateFrom->format('Y-m-d\TH:i');
        $filters['date_to'] = $dateTo->format('Y-m-d\TH:i');

        $this->terminalId = null;

        $query = $this->getListQuery($request, $filters, Currents::get($request));

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

        $saleQuery = $queryBackup->clone()->where('status_id', PaymentStatus::sale);
        $returnQuery = $queryBackup->clone()->where('status_id', PaymentStatus::return);

        $saleTotal = PriceConverter::storeToPrice($saleQuery->sum('total'));
        $saleByCard = PriceConverter::storeToPrice($saleQuery->sum('by_card'));
        $saleByCash = PriceConverter::storeToPrice($saleQuery->sum('by_cash'));

        $returnTotal = PriceConverter::storeToPrice($returnQuery->sum('total'));
        $returnByCard = PriceConverter::storeToPrice($returnQuery->sum('by_card'));
        $returnByCash = PriceConverter::storeToPrice($returnQuery->sum('by_cash'));

        $overallTotal = $saleTotal - $returnTotal;

        return APIResponse::list(
            $payments,
            ['Дата и время', '№ заказа', 'Тип, чек', 'Сумма', 'Касса, кассир', 'Идентификаторы'],
            $filters,
            $this->defaultFilters,
            [
                'terminal' => $this->terminalId,
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
        $id = $request->input('id');

        $name = $gate . '/' . $id . '.txt';

        try {
            $fiscal = Storage::disk('fiscal')->get($name);
        } catch (FileNotFoundException $e) {
            return APIResponse::error('Информация о чеке не найдена.');
        }

        return APIResponse::response([
            'fiscal' => $fiscal,
        ]);
    }

    public function export(ApiListRequest $request): JsonResponse
    {
        $this->defaultFilters['date_from'] = Carbon::now()->startOfDay()->format('Y-m-d\TH:i');
        $this->defaultFilters['date_to'] = Carbon::now()->endOfDay()->format('Y-m-d\TH:i');
        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey);

        $now = Carbon::now();

        $query = $this->getListQuery($request, $filters, Currents::get($request));

        $titles = [
            'Дата и время',
            '№ заказа',
            'Тип',
            'Чек',
            'Сумма',
            'Касса',
            'Кассир'
        ];
        $payments=$query->get();
        $payments->transform(function (Payment $payment) {
            return [
                'date' => $payment->created_at->format('d.m.Y H:i'),
                'order_id' => $payment->order_id,
                'status' => $payment->status->name,
                'fiscal' => $payment->fiscal,
                'total' => $payment->total,
                'terminal' => $payment->terminal->name ?? null,
                'position' => $payment->position ? $payment->position->user->profile->compactName : null,
            ];
        });

        $spreadsheet = new Spreadsheet();

        $spreadsheet->setActiveSheetIndex(0)->setTitle('Транзакции по кассам')->setShowRowColHeaders(true);

        $spreadsheet->getActiveSheet()->fromArray($titles, '—', 'A1');
        $spreadsheet->getActiveSheet()->fromArray($payments->toArray(), '—', 'A2');
        foreach(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'] as $col) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        ob_start();
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        $export = ob_get_clean();

        return APIResponse::response([
            'file' => base64_encode($export),
            'file_name' => 'Транзакции по кассам ' . $now->format('Y-m-d H:i'),
            'type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    protected function getListQuery(ApiListRequest $request, array $filters, Currents $current): Builder
    {
        $dateFrom = Carbon::parse($filters['date_from'])->seconds(0)->microseconds(0);
        $dateTo = Carbon::parse($filters['date_to'])->seconds(59)->microseconds(999);

        $query = Payment::query()
            ->with(['status', 'order', 'terminal', 'position', 'position.user', 'position.user.profile']);

        if ($current->isStaffAdmin() || $current->isStaffAccountant()) {
            if ($request->has('terminal_id') && $request->input('terminal_id') !== null) {
                $query->where('terminal_id', $this->terminalId = $request->input('terminal_id'));
            } else if (!empty($filters['terminal_id'])) {
                $query->where('terminal_id', $this->terminalId = $filters['terminal_id']);
            }
        } else if ($current->isStaffTerminal()) {
            $query->where('terminal_id', $this->terminalId = $current->terminalId());
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
        if ($terms = $request->search()) {
            foreach ($terms as $term) {
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
                $query->where('created_at', '>=', $dateFrom);
            }
            if (!empty($filters['date_to'])) {
                $query->where('created_at', '<=', $dateTo);
            }
        }
        return $query;
    }


}
