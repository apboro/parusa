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

        $partnerId = $current->isStaff() ? $request->input('partner_id') : $current->partnerId();

        $tripId = $request->input('trip_id');
        $excursionId = $request->input('excursion_id');
        $pierId = $request->input('pier_id');
        $shipId = $request->input('ship_id');

        $query = Ticket::query()->orderBy('updated_at', 'desc')
            ->with(
                ['status', 'order', 'order.terminal', 'order.cashier', 'order.type', 'order.partner', 'order.position', 'order.position.user.profile', 'transaction', 'grade', 'trip', 'trip.startPier', 'trip.excursion']
            )
            ->whereIn('status_id', array_merge(TicketStatus::ticket_had_paid_statuses, TicketStatus::ticket_reserved_statuses))
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
        if ($terms = $request->search()) {
            $query->where(function (Builder $query) use ($terms) {
                $query
                    ->whereIn('id', $terms)
                    ->orWhereHas('order', function (Builder $query) use ($terms) {
                        $query->whereIn('id', $terms);
                        foreach ($terms as $term) {
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
                $query->where('created_at', '>=', Carbon::parse($filters['date_from']));
                $query->where('created_at', '<=', Carbon::parse($filters['date_to']));
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
                'return_up_to' => null,
            ];
        });

        return APIResponse::list(
            $tickets,
            ['Дата и время продажи', '№ билета, заказа', 'Тип билета, стоимость', 'Комиссия, руб.', 'Рейс', 'Способ продажи', 'Продавец / Промоутер', 'Статус', 'Возврат'],
            $filters,
            $this->defaultFilters,
            []
        )->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }
}
