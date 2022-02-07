<?php

namespace App\Http\Controllers\API\Order;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketCardController extends ApiController
{
    public function get(Request $request): JsonResponse
    {
        $current = Currents::get($request);

        $ticket = Ticket::query()
            ->where('id', $request->input('id'))
            ->when(!$current->isStaff(), function (Builder $query) use ($current) {
                $query->whereHas('order', function (Builder $query) use ($current) {
                    $query->where('partner_id', $current->partnerId());
                });
            })
            ->with(['status', 'order', 'order.type', 'order.partner', 'order.position.user.profile', 'trip', 'trip.excursion', 'trip.startPier', 'grade'])
            ->first();

        if ($ticket === null) {
            return APIResponse::error('Билет не найден');
        }

        /** @var Ticket $ticket */

        return APIResponse::response([
            'order_id' => $ticket->order_id,
            'is_order_reserve' => $ticket->order->hasStatus(OrderStatus::partner_reserve),
            'sold_at' => $ticket->created_at->format('d.m.Y, H:i'),
            'order_type' => $ticket->order->type->name,
            'position' => $ticket->order->partner->name,
            'partner' => $ticket->order->position->user->profile->fullName,
            'status' => $ticket->status->name,
            'excursion' => $ticket->trip->excursion->name,
            'pier' => $ticket->trip->startPier->name,
            'trip_id' => $ticket->trip_id,
            'trip_start_date' => $ticket->trip->start_at->format('d.m.Y'),
            'trip_start_time' => $ticket->trip->start_at->format('H:i'),
            'grade' => $ticket->grade->name,
            'base_price' => $ticket->base_price,

            'name' => $ticket->order->name,
            'email' => $ticket->order->email,
            'phone' => $ticket->order->phone,
        ]);
    }
}
