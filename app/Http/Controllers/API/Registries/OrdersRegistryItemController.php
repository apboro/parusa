<?php

namespace App\Http\Controllers\API\Registries;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdersRegistryItemController extends ApiController
{
    public function view(Request $request): JsonResponse
    {
        $current = Currents::get($request);

        $query = Order::query()
            ->where('id', $request->input('id'))
            ->with([
                'status', 'type', 'tickets.grade', 'partner', 'position.user.profile', 'terminal',
                'tickets', 'tickets.status', 'tickets.trip', 'tickets.trip.excursion', 'tickets.trip.startPier',
            ]);

        if ($current->isRepresentative()) {
            $query->where('partner_id', $current->partnerId());
        } else if ($current->isStaffTerminal()) {
            if ($request->input('reserve')) {
                $query->whereIn('status_id', OrderStatus::order_reserved_statuses);
            } else {
                $query->where('terminal_id', $current->terminalId());
            }
        } else if ($current->isStaffAdmin()) {
            if ($request->input('partner_id')) {
                $query->where('partner_id', $request->input('partner_id'));
            }
        } else {
            return APIResponse::error('Неверно заданы параметры');
        }

        /** @var ?Order $order */
        $order = $query->first();

        if ($order === null) {
            return APIResponse::error('Заказ не найден');
        }


        $reserveValidUntil = $order->reserveValidUntil();

        if ($current->isStaffTerminal()) {
            $returnable = $order->hasStatus(OrderStatus::terminal_paid) || $order->hasStatus(OrderStatus::terminal_partial_returned);
        } else if ($current->isRepresentative()) {
            $returnable = $order->hasStatus(OrderStatus::partner_paid) || $order->hasStatus(OrderStatus::partner_partial_returned);
        } else {
            $returnable = false;
        }

        return APIResponse::response([
            'status' => $order->status->name,
            'is_reserve' => $order->hasStatus(OrderStatus::partner_reserve),
            'valid_until' => $reserveValidUntil ? $reserveValidUntil->format('H:i d.m.Y') : null,
            'date' => $order->created_at->format('d.m.Y'),
            'time' => $order->created_at->format('H:i'),
            'type' => $order->type->name,
            'terminal' => $order->terminal->name ?? null,
            'partner' => $order->partner->name ?? null,
            'position' => $order->position->user->profile->fullName ?? null,
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
                    'returnable' => in_array($ticket->status_id, TicketStatus::ticket_returnable_statuses, true),
                ];
            }),
            'total' => $order->tickets->sum('base_price'),
            'tickets_count' => $order->tickets->count(),
            'name' => $order->name,
            'email' => $order->email,
            'phone' => $order->phone,
            'can_buy' => $current->isRepresentative() || $current->isStaffTerminal(),
            'can_return' => $current->isRepresentative() || $current->isStaffTerminal(),
            'returnable' => $returnable,
            'is_actual' => in_array($order->status_id, OrderStatus::order_returnable_statuses, true),
            'is_printable' => in_array($order->status_id, OrderStatus::order_printable_statuses, true)
                && $order->tickets()->whereIn('status_id', TicketStatus::ticket_printable_statuses)->count() > 0,
        ]);
    }
}
