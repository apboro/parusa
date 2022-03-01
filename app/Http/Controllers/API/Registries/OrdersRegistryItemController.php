<?php

namespace App\Http\Controllers\API\Registries;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Tickets\Order;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdersRegistryItemController extends ApiController
{
    public function view(Request $request): JsonResponse
    {
        $current = Currents::get($request);

        $order = Order::query()
            ->where('id', $request->input('id'))
            ->when(!$current->isStaff(), function (Builder $query) use ($current) {
                $query->where('partner_id', $current->partnerId());
            })
            ->with(['status', 'type', 'tickets.grade', 'partner', 'position.user.profile', 'tickets', 'tickets.status', 'tickets.trip', 'tickets.trip.excursion', 'tickets.trip.startPier'])
            ->first();

        if ($order === null) {
            return APIResponse::error('Заказ не найден');
        }

        /** @var Order $order */

        $reserveValidUntil = $order->reserveValidUntil();

        return APIResponse::response([
            'status' => $order->status->name,
            'is_reserve' => $order->hasStatus(OrderStatus::partner_reserve),
            'valid_until' => $reserveValidUntil ? $reserveValidUntil->format('H:i d.m.Y') : null,
            'date' => $order->created_at->format('d.m.Y'),
            'time' => $order->created_at->format('H:i'),
            'type' => $order->type->name,
            'partner' => $order->partner->name,
            'position' => $order->position->user->profile->fullName,
            'tickets' => $order->tickets->map(function (Ticket $ticket) {
                return [
                    'id' => $ticket->id,
                    'base_price' => $ticket->base_price,
                    'trip_id' => $ticket->trip_id,
                    'trip_start_date' => $ticket->trip->start_at->format('d.m.Y'),
                    'trip_start_time' => $ticket->trip->start_at->format('H:i'),
                    'excursion' => $ticket->trip->excursion->name,
                    'pier' => $ticket->trip->startPier->name,
                    'grade' => $ticket->grade->name,
                    'status' => $ticket->status->name,
                ];
            }),
            'total' => $order->tickets->sum('base_price'),
            'tickets_count' => $order->tickets->count(),
            'name' => $order->name,
            'email' => $order->email,
            'phone' => $order->phone,
            'can_buy' => !$current->isStaff(),
        ]);
    }
}
