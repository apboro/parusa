<?php

namespace App\Http\Controllers\API\Registries;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class TicketsRegistryController extends ApiController
{
    protected array $defaultFilters = [
        'date_from' => null,
        'date_to' => null,
        'order_type_id' => null,
        'trip_date' => null,
        'excursion_id' => null,
        'terminal_id' => null,
    ];

    protected array $rememberFilters = [
        'date_from',
        'date_to',
        'order_type_id',
        'trip_date',
        'excursion_id',
        'terminal_id',
    ];

    protected string $rememberKey = CookieKeys::ticket_registry_list;

    /**
     * Get tickets list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        $current = Currents::get($request);
        //TODO add check request from which page
        $this->defaultFilters['date_from'] = Carbon::now()->startOfDay()->format('Y-m-d\TH:i');
        $this->defaultFilters['date_to'] = Carbon::now()->endOfDay()->format('Y-m-d\TH:i');
        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey);

        if (!$filters['date_from']) $filters['date_from'] = $this->defaultFilters['date_from'];
        if (!$filters['date_to']) $filters['date_to'] = $this->defaultFilters['date_to'];

        $partnerId = $current->isStaff() ? $request->input('partner_id') : $current->partnerId();
        $tripId = $request->input('trip_id');
        $excursionId = $request->input('excursion_id');
        $pierId = $request->input('pier_id');
        $shipId = $request->input('ship_id');

        $query = $this->getListQuery($request->search(), $filters, $partnerId, $tripId, $excursionId, $pierId, $shipId);

        $tickets = $query->paginate($request->perPage());

        /** @var LengthAwarePaginator $tickets */
        $tickets->transform(function (Ticket $ticket) {
            if ($ticket->transaction) {
                $commissionType = $ticket->transaction->commission_type === 'fixed' ? 'фикс.' : $ticket->transaction->commission_value . '%';
            }
            return [
                'date' => $ticket->created_at->format('d.m.Y'),
                'time' => $ticket->created_at->format('H:i'),
                'order_id' => $ticket->order_id,
                'neva_travel_order_number' => $ticket->order->additionalData?->provider_order_id,
                'id' => $ticket->id,
                'type' => $ticket->grade->name,
                'amount' => $ticket->base_price,
                'status' => $ticket->status->name,
                'commission_type' => $commissionType ?? null,
                'commission_amount' => $ticket->transaction !== null ? $ticket->transaction->amount : null,
                'trip_date' => $ticket->trip->start_at->format('d.m.Y'),
                'trip_time' => $ticket->trip->start_at->format('H:i'),
                'trip_id' => $ticket->trip_id,
                'excursion' => $ticket->trip->excursion->name,
                'pier' => $ticket->trip->startPier->name,
                'order_type' => $ticket->order->type->name,
                'partner' => $ticket->order->partner->name ?? null,
                'sale_by' => $ticket->order->position ? $ticket->order->position->user->profile->compactName : null,
                'terminal' => $ticket->order->terminal->name ?? null,
                'cashier' => $ticket->order->cashier ? $ticket->order->cashier->user->profile->compactName : null,
                'buyer_name' => $ticket->order->name,
                'buyer_email' => $ticket->order->email,
                'buyer_phone' => $ticket->order->phone,
                'return_up_to' => null,
            ];
        });

        return APIResponse::list(
            $tickets,
            ['Дата и время продажи', '№ билета, заказа', 'Тип билета, стоимость', 'Комиссия, руб.', 'Рейс', 'Способ продажи', 'Покупатель', 'Статус', 'Возврат'],
            $filters,
            $this->defaultFilters,
            []
        )->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }

    public function export(ApiListRequest $request): JsonResponse
    {
        $current = Currents::get($request);
        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey);

        $dateFrom = Carbon::parse($filters['date_from'])->seconds(0)->microseconds(0);
        $filters['date_from'] = $dateFrom->format('Y-m-d\TH:i');

        $dateTo = Carbon::parse($filters['date_to'])->seconds(59)->microseconds(999);
        $filters['date_to'] = $dateTo->format('Y-m-d\TH:i');

        $partnerId = $current->isStaff() ? $request->input('partner_id') : $current->partnerId();
        $tripId = $request->input('trip_id');
        $excursionId = $request->input('excursion_id');
        $pierId = $request->input('pier_id');
        $shipId = $request->input('ship_id');

        $tickets = $this->getListQuery($request->search(), $filters, $partnerId, $tripId, $excursionId, $pierId, $shipId)->get();

        $titles = [
            '№ билета',
            '№ заказа',
            '№ заказа НТ',
            'Дата продажи',
            'Время продажи',
            'Тип билета',
            'Стоимость',
            'Комиссия, руб.',
            'Рейс',
            'Дата рейса',
            'Номер рейса',
            'Способ продажи',
            'Продавец',
            'Промоутер',
            'Статус',
            'Имя',
            'Телефон',
            'Почта',
        ];

        $tickets->transform(function (Ticket $ticket) {
            return [
                'id' => $ticket->id,
                'order_id' => $ticket->order_id,
                'neva_travel_order_number' => $ticket->order->additionalData?->provider_order_id,
                'date' => $ticket->created_at->format('d.m.Y'),
                'time' => $ticket->created_at->format('H:i'),
                'type' => $ticket->grade->name,
                'amount' => $ticket->base_price,
                'commission_amount' => $ticket->transaction !== null ? $ticket->transaction->amount : null,
                'excursion' => $ticket->trip->excursion->name,
                'excursion_date' => $ticket->trip->start_at->format('d.m.Y H:i'),
                'trip_id' => $ticket->trip_id,
                'order_type' => $ticket->order->type->name,
                'partner' => $ticket->order->partner->name ?? null,
                'sale_by' => $ticket->order->position ? $ticket->order->position->user->profile->compactName : null,
                'status' => $ticket->status->name,
                'buyer_name' => $ticket->order->name,
                'buyer_phone' => $ticket->order->phone,
                'buyer_email' => $ticket->order->email,
            ];
        });
        $spreadsheet = new Spreadsheet();

        $spreadsheet->setActiveSheetIndex(0)->setTitle('Транзакции')->setShowRowColHeaders(true);

        $spreadsheet->getActiveSheet()->fromArray($titles, '—', 'A1');
        $spreadsheet->getActiveSheet()->fromArray($tickets->toArray(), '—', 'A2');
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R'] as $col) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        ob_start();
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        $export = ob_get_clean();

        return APIResponse::response([
            'file' => base64_encode($export),
            'file_name' => 'Билеты ' . Carbon::now()->format('Y-m-d H:i'),
            'type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Build tickets list query.
     *
     * @param array $search
     * @param array $filters
     * @param int|null $partnerId
     * @param int|null $tripId
     * @param int|null $excursionId
     * @param int|null $pierId
     * @param int|null $shipId
     *
     * @return Builder
     */
    protected function getListQuery(array $search, array $filters, ?int $partnerId, ?int $tripId, ?int $excursionId, ?int $pierId, ?int $shipId): Builder
    {
        $query = Ticket::query()->orderBy('updated_at', 'desc')
            ->with(
                ['status', 'order', 'order.terminal', 'order.cashier', 'order.type', 'order.partner', 'order.position', 'order.position.user.profile', 'transaction', 'grade', 'trip', 'trip.startPier', 'trip.excursion']
            )
            ->whereIn('status_id', array_merge(TicketStatus::ticket_had_paid_statuses, TicketStatus::ticket_reserved_statuses, [TicketStatus::used]))
            ->when($partnerId, function (Builder $query) use ($partnerId) {
                $query->whereHas('order', function (Builder $query) use ($partnerId) {
                    $query->where('partner_id', $partnerId);
                });
            })
            ->when($tripId, function (Builder $query) use ($tripId) {
                $query->where('trip_id', $tripId);
            })
            ->when(($excursionId || !empty($filters['excursion_id'])), function (Builder $query) use ($excursionId, $filters) {
                $query->whereHas('trip', function (Builder $query) use ($excursionId, $filters) {
                    $query->where('excursion_id', $excursionId ?? $filters['excursion_id']);
                });
            })
            ->when($pierId, function (Builder $query) use ($pierId) {
                $query->whereHas('trip', function (Builder $query) use ($pierId) {
                    $query->where('pier_id', $pierId);
                });
            })
            ->when($shipId, function (Builder $query) use ($shipId) {
                $query->whereHas('trip', function (Builder $query) use ($shipId) {
                    $query->where('ship_id', $shipId);
                });
            });

        // apply search
        if ($search) {
            $query->where(function (Builder $query) use ($search) {
                $query
                    ->whereIn('id', $search)
                    ->orWhereHas('order', function (Builder $query) use ($search) {
                        $query->whereIn('id', $search);
                        foreach ($search as $term) {
                            $query->orWhere('name', 'LIKE', '%' . $term . '%')
                                ->orWhere('email', 'LIKE', '%' . $term . '%')
                                ->orWhere('phone', 'LIKE', '%' . $term . '%');
                        }
                    });
            });
        } else {
            // apply filters
            if (!$tripId) {
                if (empty($filters['date_from'])) {
                    $filters['date_from'] = $this->defaultFilters['date_from'];
                }
                if (empty($filters['date_to'])) {
                    $filters['date_to'] = $this->defaultFilters['date_to'];
                }
                $query->whereDate('created_at', '>=', Carbon::parse($filters['date_from']));
                $query->whereDate('created_at', '<=', Carbon::parse($filters['date_to']));
            }
            if (!empty($filters['order_type_id'])) {
                $query->whereHas('order', function (Builder $query) use ($filters) {
                    $query->where('type_id', $filters['order_type_id']);
                });
            }
            if (!empty($filters['terminal_id'])) {
                $query->whereHas('order', function (Builder $query) use ($filters) {
                    $query->where('terminal_id', $filters['terminal_id']);
                });
            }
            if (!empty($filters['trip_date'])) {
                $query->whereHas('trip', function (Builder $query) use ($filters) {
                    $query->whereDate('start_at', Carbon::parse($filters['trip_date']));
                });
            }
        }
        return $query;
    }
}
