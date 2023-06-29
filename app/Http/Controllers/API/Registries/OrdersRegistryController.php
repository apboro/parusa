<?php

namespace App\Http\Controllers\API\Registries;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class OrdersRegistryController extends ApiController
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

        $this->defaultFilters['date_from'] = Carbon::now()->day(1)->format('Y-m-d');
        $this->defaultFilters['date_to'] = Carbon::now()->format('Y-m-d');
        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey);

        $query = Order::query()->orderBy('updated_at', 'desc')
            ->with([
                'type', 'status',
                'tickets', 'tickets.status', 'tickets.trip', 'tickets.trip.excursion', 'tickets.trip.startPier', 'tickets.grade',
                'partner', 'position', 'position.user', 'position.user.profile', 'terminal', 'cashier',
                'promocode',
            ])
            ->withCount(['tickets'])
            ->whereIn('status_id', OrderStatus::order_had_paid_statuses);

        if ($current->isRepresentative()) {
            $query->where('partner_id', $current->partnerId());
        } else if ($current->isStaffTerminal() && empty($current->terminal()->show_all_orders)) {
            $query->where('terminal_id', $current->terminalId());
        } else if (($current->isStaffAdmin() || $current->isStaffOfficeManager() || $current->isStaffAccountant() || $current->isStaffPiersManager())
            || ($current->isStaffTerminal() && !empty($current->terminal()->show_all_orders))) {
            if ($request->input('partner_id') !== null) {
                $query->where('partner_id', $request->input('partner_id'));
            }
            if ($request->input('terminal_id') !== null) {
                $query->where('terminal_id', $request->input('terminal_id'));
            }
        } else {
            return APIResponse::error('Неверно заданы параметры');
        }

        // apply search
        if ($terms = $request->search()) {
            $query->where(function (Builder $query) use ($terms, $current, $request) {
                $query->whereIn('id', $terms);

                foreach ($terms as $term) {
                    $query->orWhere('name', 'LIKE', '%' . $term . '%')
                        ->orWhere('email', 'LIKE', '%' . $term . '%');
                }

                if (!$current->isStaffTerminal() || !$request->input('only_orders')) {
                    $query->orWhereHas('tickets', function (Builder $query) use ($terms) {
                        $query->whereIn('id', $terms);
                    });
                }
            });
        } else {
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
            if (!empty($filters['search_phone'])) {
                $query->where('phone', 'LIKE', '%' . $filters['search_phone'] . '%');
            }
        }

        $orders = $query->paginate($request->perPage());

        /** @var LengthAwarePaginator $orders */
        $orders->transform(function (Order $order) use ($current) {

            if ($order->promocode->count() > 0) {
                $returnable = false;
            } else if ($current->isStaffTerminal()) {
                $returnable = $order->hasStatus(OrderStatus::terminal_paid) || $order->hasStatus(OrderStatus::terminal_partial_returned);
            } else if ($current->isRepresentative()) {
                $returnable = $order->hasStatus(OrderStatus::partner_paid) || $order->hasStatus(OrderStatus::partner_partial_returned);
            } else {
                $returnable = false;
            }

            return
                [
                    'id' => $order->id,
                    'date' => $order->created_at->format('d.m.Y, H:i'),
                    'tickets_total' => $order->getAttribute('tickets_count'),
                    'amount' => $order->tickets->sum('base_price'),
                    'order_total' => $order->total(),
                    'returnable' => $returnable,
                    'payment_unconfirmed' => (bool)$order->payment_unconfirmed,
                    'info' => [
                        'buyer_name' => $order->name,
                        'buyer_email' => $order->email,
                        'buyer_phone' => $order->phone,
                        'order_type' => $order->type->name,
                        'partner_id' => $order->partner_id,
                        'partner' => $order->partner->name ?? null,
                        'position_id' => $order->position_id,
                        'position_name' => $order->position ? $order->position->user->profile->compactName : null,
                        'terminal_id' => $order->terminal_id,
                        'terminal_name' => $order->terminal->name ?? null,
                        'cashier' => $order->cashier ? $order->cashier->user->profile->compactName : null,
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
            ['№ заказа', 'Дата оплаты заказа', 'Покупатель', 'Информация о заказе', 'Билетов в заказе', 'Стоимость заказа'],
            $filters,
            $this->defaultFilters,
            []
        );
    }
}
