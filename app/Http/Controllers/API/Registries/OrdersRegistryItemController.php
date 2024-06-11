<?php

namespace App\Http\Controllers\API\Registries;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\PromoCode\PromoCode;
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
                'status', 'type', 'tickets.grade', 'partner', 'position.user.profile', 'terminal', 'cashier',
                'tickets', 'tickets.status', 'tickets.trip', 'tickets.trip.excursion', 'tickets.trip.startPier',
                'promocode'
            ]);

        if ($current->isRepresentative()) {
            $query->where('partner_id', $current->partnerId());
        } else if ($current->isStaffTerminal() && empty($current->terminal()->show_all_orders)) {
            if ($request->input('reserve')) {
                $query->whereIn('status_id', OrderStatus::order_reserved_statuses);
            } else {
                $query->where('terminal_id', $current->terminalId());
            }
        } else if (($current->isStaffAdmin() || $current->isStaffOfficeManager() || $current->isStaffAccountant() || $current->isStaffPiersManager())
            || ($current->isStaffTerminal() && !empty($current->terminal()->show_all_orders))) {
            if ($request->input('partner_id')) {
                $query->where('partner_id', $request->input('partner_id'));
            }
        } else {
            return APIResponse::error('Неверно заданы параметры');
        }

        /** @var Order|null $order */
        $order = $query->first();

        if ($order === null) {
            return APIResponse::error('Заказ не найден');
        }

        $reserveValidUntil = $order->reserveValidUntil();

        /** @var PromoCode|null $promocode */
        $promocode = $order->promocode->first();

//        if ($promocode !== null) {
//            $returnable = false;
//        } else
        if ($current->isStaffTerminal()) {
            $returnable = $order->hasStatus(OrderStatus::terminal_paid) || $order->hasStatus(OrderStatus::terminal_partial_returned);
        } else if ($current->isRepresentative()) {
            $returnable = in_array($order->status_id, [OrderStatus::partner_paid, OrderStatus::api_confirmed, OrderStatus::partner_partial_returned]);
        } else if ($current->isStaffAdmin()) {
            $returnable = in_array($order->status_id,[OrderStatus::showcase_paid,
                    OrderStatus::showcase_partial_returned, OrderStatus::promoter_paid, OrderStatus::partner_paid_by_link]);
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
            'promocode' => $promocode->code ?? null,
            'cashier' => $order->cashier ? $order->cashier->user->profile->fullName : null,
            'partner' => $order->partner->name ?? null,
            'position' => $order->position ? $order->position->user->profile->fullName : null,
            'tickets' => $order->tickets->map(function (Ticket $ticket) {
                return [
                    'id' => $ticket->id,
                    'provider_id' => $ticket->provider_id,
                    'base_price' => $ticket->getPrice(),
                    'trip_id' => $ticket->trip_id,
                    'trip_start_date' => $ticket->trip->start_at->format('d.m.Y'),
                    'trip_start_time' => $ticket->trip->start_at->format('H:i'),
                    'excursion' => $ticket->trip->excursion->name,
                    'is_single_ticket' => $ticket->trip->excursion->is_single_ticket,
                    'reverse_excursion_id' => $ticket->trip->excursion->reverse_excursion_id,
                    'excursion_id' => $ticket->trip->excursion->id,
                    'transferable' => in_array($ticket->status_id, TicketStatus::ticket_paid_statuses, true) && $ticket->provider_id !== Provider::city_tour,
                    'isBackward' => $ticket->isBackward(),
                    'pier' => $ticket->startPier?->name ?? $ticket->trip->startPier->name,
                    'grade' => $ticket->grade->name,
                    'status' => $ticket->status->name,
                    'returnable' => in_array($ticket->status_id, TicketStatus::ticket_returnable_statuses, true),
                ];
            }),
            'total' => $order->tickets->sum('base_price'),
            'order_total' => $order->total(),
            'tickets_count' => $order->tickets->count(),
            'name' => $order->name,
            'email' => $order->email,
            'phone' => $order->phone,
            'can_send_sms' => $current->partner()?->profile->can_send_sms,
            'self_pay' => $current->partner()?->profile->self_pay,
            'can_buy' => $current->isRepresentative() || $current->isStaffTerminal(),
            'can_return' => $current->isRepresentative() || $current->isStaffAdmin(), // terminal users can not return tickets from CRM yet -> || $current->isStaffTerminal(),
            'returnable' => $returnable,
            'cant_partly_return' => $order->tickets->contains(function (Ticket $ticket) {
               return $ticket->provider_id !== Provider::scarlet_sails;
            }),
            'is_actual' => in_array($order->status_id, OrderStatus::order_returnable_statuses, true),
            'is_printable' => in_array($order->status_id, OrderStatus::order_printable_statuses, true)
                && $order->tickets()->whereIn('status_id', TicketStatus::ticket_printable_statuses)->count() > 0,
        ]);
    }
}
