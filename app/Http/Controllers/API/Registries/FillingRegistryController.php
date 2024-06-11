<?php

namespace App\Http\Controllers\API\Registries;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\PartnerType;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Partner\Partner;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class FillingRegistryController extends ApiController
{
    protected array $defaultFilters = [
        'date_from' => null,
        'date_to' => null,
        'trip_date' => null,
        'excursion_ids' => null,
        'partner_id' => null,
        'ticket_status_id' => null
    ];

    protected array $rememberFilters = [
        'date_from',
        'date_to',
        'trip_date',
    ];

    protected string $rememberKey = CookieKeys::filling_registry_list;

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

        $query = $this->getListQuery($request->search(), $filters);

        $allTickets = $query->clone()->get();
        $totals = [];
        $totals['ticketsCount'] = $allTickets->count();

        $totals['totalSum'] = null;
        foreach ($allTickets as $ticket) {
            if ($ticket->order->promocode->isNotEmpty()) {
                $amountToMinus = $ticket->order->promocode[0]->amount / $ticket->order->tickets->count();
                $totals['totalSum'] += $ticket->base_price - $amountToMinus;
            } else {
                $totals['totalSum'] += $ticket->base_price;
            }
        }

        $tickets = $query->paginate($request->perPage());

        /** @var LengthAwarePaginator $tickets */
        $tickets->transform(function (Ticket $ticket) {
            return [
                'date' => $ticket->created_at->format('d.m.Y'),
                'time' => $ticket->created_at->format('H:i'),
                'order_id' => $ticket->order_id,
                'id' => $ticket->id,
                'type' => $ticket->grade->name,
                'amount' => $ticket->order->promocode ? $ticket->order->total()/$ticket->order->tickets->count() : $ticket->base_price,
                'status' => $ticket->status->name,
                'trip_date' => $ticket->trip->start_at->format('d.m.Y'),
                'trip_time' => $ticket->trip->start_at->format('H:i'),
                'trip_id' => $ticket->trip_id,
                'excursion' => $ticket->trip->excursion->name,
                'pier' => $ticket->startPier?->name ?? $ticket->trip->startPier->name,
                'return_up_to' => null,
            ];
        });

        return APIResponse::list(
            $tickets,
            ['Дата и время продажи', '№ билета, заказа', 'Тип билета, стоимость', 'Рейс', 'Статус', 'Возврат'],
            $filters,
            $this->defaultFilters,
            [
                'partners' => Partner::query()
                    ->where('type_id', PartnerType::ship_owner)
                    ->get(),
                'totals' => $totals,
                'dateFrom' => Carbon::parse($filters['date_from'])->format('d.m.y, H:i'),
                'dateTo' => Carbon::parse($filters['date_to'])->format('d.m.y, H:i'),
                'partner' => $filters['partner_id'] ? Partner::findOrFail($filters['partner_id'])->name : "Все"
            ]
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

        $tickets = $this->getListQuery($request->search(), $filters)->get();

        $titles = [
            '№ билета',
            '№ заказа',
            'Дата продажи',
            'Время продажи',
            'Тип билета',
            'Стоимость',
            'Рейс',
            'Дата рейса',
            'Номер рейса',
            'Статус',
        ];

        $tickets->transform(function (Ticket $ticket) {
            return [
                'id' => $ticket->id,
                'order_id' => $ticket->order_id,
                'date' => $ticket->created_at->format('d.m.Y'),
                'time' => $ticket->created_at->format('H:i'),
                'type' => $ticket->grade->name,
                'amount' => $ticket->base_price,
                'excursion' => $ticket->trip->excursion->name,
                'excursion_date' => $ticket->trip->start_at->format('d.m.Y H:i'),
                'trip_id' => $ticket->trip_id,
                'status' => $ticket->status->name,
            ];
        });
        $spreadsheet = new Spreadsheet();

        $spreadsheet->setActiveSheetIndex(0)->setTitle('Транзакции')->setShowRowColHeaders(true);

        $spreadsheet->getActiveSheet()->fromArray($titles, '—', 'A1');
        $spreadsheet->getActiveSheet()->fromArray($tickets->toArray(), '—', 'A2');
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'] as $col) {
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
    protected function getListQuery(array $search, array $filters): Builder
    {
        $query = Ticket::query()->orderBy('created_at')
            ->with(
                [
                    'status', 'order', 'order.terminal', 'order.cashier',
                    'order.type', 'order.partner', 'order.promocode',
                    'order.position', 'order.position.user.profile',
                    'transaction', 'grade', 'trip',
                    'trip.startPier', 'trip.excursion', 'trip.ship'
                ]
            )
            ->whereIn('status_id', array_merge(TicketStatus::ticket_had_paid_statuses, TicketStatus::ticket_reserved_statuses, [TicketStatus::used]))
            ->whereHas('additionalData', fn(Builder $query) => $query->whereNotNull('shipowner_partner_id'))
            ->when($filters['partner_id'], function (Builder $query) use ($filters) {
                $query->whereHas('additionalData', function (Builder $query) use ($filters) {
                    $query->where('shipowner_partner_id', $filters['partner_id']);
                });
            })
            ->when(!empty($filters['excursion_ids']), function (Builder $query) use ($filters) {
                $query->whereHas('trip', function (Builder $query) use ($filters) {
                    $query->whereIn('excursion_id', $filters['excursion_ids']);
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
            if (empty($filters['date_from'])) {
                $filters['date_from'] = $this->defaultFilters['date_from'];
            }
            if (empty($filters['date_to'])) {
                $filters['date_to'] = $this->defaultFilters['date_to'];
            }
            $query->where('created_at', '>=', Carbon::parse($filters['date_from']));
            $query->where('created_at', '<=', Carbon::parse($filters['date_to']));
        }
        if (!empty($filters['trip_date'])) {
            $query->whereHas('trip', function (Builder $query) use ($filters) {
                $query->whereDate('start_at', Carbon::parse($filters['trip_date']));
            });
        }
        return $query;
    }
}
