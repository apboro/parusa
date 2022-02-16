<?php

namespace App\Http\Controllers\API\Registries;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class TicketsRegistry extends ApiController
{
    protected array $defaultFilters = [
        'date_from' => null,
        'date_to' => null,
        'order_type_id' => null,
    ];

    protected array $rememberFilters = [
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

        $this->defaultFilters['date_from'] = Carbon::now()->day(1)->format('Y-m-d');
        $this->defaultFilters['date_to'] = Carbon::now()->format('Y-m-d');
        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey);

        $partnerId = $current->isStaff() ? $request->input('partner_id') : $current->partnerId();

        $tripId = $request->input('trip_id');
        $excursionId = $request->input('excursion_id');
        $pierId = $request->input('pier_id');
        $shipId = $request->input('ship_id');

        $query = Ticket::query()
            ->with(['status', 'order', 'order.type', 'order.partner', 'order.position', 'order.position.user.profile', 'transaction', 'grade', 'trip', 'trip.startPier', 'trip.excursion'])
            ->when($partnerId, function (Builder $query) use ($partnerId) {
                $query->whereHas('order', function (Builder $query) use ($partnerId) {
                    $query->where('partner_id', $partnerId);
                });
            })
            ->when($tripId, function (Builder $query) use ($tripId) {
                $query->where('trip_id', $tripId);
            })
            ->when($excursionId, function (Builder $query) use ($excursionId) {
                $query->whereHas('trip', function (Builder $query) use ($excursionId) {
                    $query->where('excursion_id', $excursionId);
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

        // apply filters
        if (!$tripId) {
            if (!empty($filters['date_from'])) {
                $query->whereDate('created_at', '>=', Carbon::parse($filters['date_from']));
            }
            if (!empty($filters['date_to'])) {
                $query->whereDate('created_at', '<=', Carbon::parse($filters['date_to']));
            }
        }
        if (!empty($filters['order_type_id'])) {
            $query->whereHas('order', function (Builder $query) use ($filters) {
                $query->where('type_id', $filters['order_type_id']);
            });
        }

        // apply search
        if ($terms = $request->search()) {
            $query->where(function (Builder $query) use ($terms) {
                $query
                    ->whereIn('id', $terms)
                    ->orWhereHas('order', function (Builder $query) use ($terms) {
                        $query->whereIn('id', $terms);
                    });
            });
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
                'partner' => $ticket->order->partner->name,
                'sale_by' => $ticket->order->position ? $ticket->order->position->user->profile->compactName : null,
                'return_up_to' => null,
            ];
        });

        return APIResponse::list(
            $tickets,
            ['Дата и время продажи', '№ заказа, билета', 'Тип билета, стоимость', 'Комиссия, руб.', 'Рейс', 'Способ продажи', 'Продавец', 'Статус', 'Возврат'],
            $filters,
            $this->defaultFilters,
            []
        );
    }
}
