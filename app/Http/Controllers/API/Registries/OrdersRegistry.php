<?php

namespace App\Http\Controllers\API\Registries;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Tickets\Order;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class OrdersRegistry extends ApiController
{
    protected array $defaultFilters = [
        'date_from' => null,
        'date_to' => null,
        'order_type_id' => null,
    ];

    protected array $rememberFilters = [
    ];

    protected string $rememberKey = CookieKeys::order_registry_list;

    /**
     * Get orders list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        $current = Currents::get($request);

        $this->defaultFilters['date_from'] = Carbon::now()->day(1)->format('d.m.Y');
        $this->defaultFilters['date_to'] = Carbon::now()->format('d.m.Y');
        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey);

        $query = Order::query()
            ->with(['type', 'status', 'tickets', 'tickets.status', 'tickets.trip', 'tickets.trip.excursion', 'tickets.trip.startPier', 'tickets.grade', 'partner', 'position', 'position.user', 'position.user.profile'])
            ->withCount(['tickets'])
            ->when(!$current->isStaff(), function (Builder $query) use ($current) {
                $query->where('partner_id', $current->partnerId());
            })
            ->when($current->isStaff() && ($request->input('partner_id')), function (Builder $query) use ($request) {
                $query->where('partner_id', $request->input('partner_id'));
            })
            ->whereIn('status_id', OrderStatus::order_paid_statuses);

        // apply filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', Carbon::parse($filters['date_from']));
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', Carbon::parse($filters['date_to']));
        }
        if (!empty($filters['order_type_id'])) {
            $query->where('type_id', $filters['order_type_id']);
        }

        // apply search
        if ($terms = $request->search()) {
            $query->where(function (Builder $query) use ($terms) {
                $query
                    ->whereIn('id', $terms)
                    ->orWhereHas('tickets', function (Builder $query) use ($terms) {
                        $query->whereIn('id', $terms);
                    });
            });
        }

        $orders = $query->paginate($request->perPage(10));

        /** @var LengthAwarePaginator $orders */
        $orders->transform(function (Order $order) {
            return
                [
                    'id' => $order->id,
                    'date' => $order->created_at->format('d.m.Y, H:i'),
                    'tickets_total' => $order->getAttribute('tickets_count'),
                    'amount' => $order->tickets->sum('base_price'),
                    'info' => [
                        'buyer_name' => $order->name,
                        'buyer_email' => $order->email,
                        'buyer_phone' => $order->phone,
                        'order_type' => $order->type->name,
                        'partner_id' => $order->partner_id,
                        'partner' => $order->partner->name,
                        'position_id' => $order->position_id,
                        'position_name' => $order->position ? $order->position->user->profile->compactName : null,
                    ],
                    'tickets' => $order->tickets->map(function (Ticket $ticket) {
                        return [
                            'id' => $ticket->id,
                            'trip_date' => $ticket->trip->start_at->format('d.m.Y'),
                            'trip_time' => $ticket->trip->start_at->format('H:i'),
                            'excursion' => $ticket->trip->excursion->name,
                            'pier' => $ticket->trip->startPier->name,
                            'type' => $ticket->grade->name,
                            'amount' => $ticket->base_price,
                            'status' => $ticket->status->name,
                        ];
                    }),
                ];
        });

        return APIResponse::list(
            $orders,
            ['№ заказа', 'Дата оплаты заказа', 'Информация о заказе', 'Билетов в заказе', 'Стоимость заказа'],
            $filters,
            $this->defaultFilters,
            []
        );
    }
}
