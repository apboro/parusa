<?php

namespace App\Http\Controllers\API\Cart;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Hit\Hit;
use App\Models\Order\Order;
use App\Models\Positions\PositionOrderingTicket;
use App\Models\Sails\Trip;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TerminalCartController extends ApiEditController
{
    /**
     * Get current cart.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function get(Request $request): JsonResponse
    {
        Hit::register(HitSource::terminal);
        $current = Currents::get($request);

        if (!$current->position() || !$current->isStaffTerminal()) {
            return APIResponse::error('Вы не можете оформлять заказы.');
        }

        /** @var ?Order $order */
        $order = Order::query()
            ->with(['status', 'tickets', 'tickets.grade', 'tickets.trip', 'tickets.trip.excursion', 'tickets.trip.startPier'])
            ->where(['terminal_position_id' => $current->positionId(), 'terminal_id' => $current->terminalId()])
            ->whereIn('status_id', OrderStatus::terminal_processing_statuses)
            ->first();

        $orderTickets = $order?->tickets->map(function (Ticket $ticket) {
            return [
                'id' => $ticket->id,
                'trip_id' => $ticket->trip->id,
                'trip_start_date' => $ticket->trip->start_at->format('d.m.Y'),
                'trip_start_time' => $ticket->trip->start_at->format('H:i'),
                'excursion' => $ticket->trip->excursion->name,
                'pier' => $ticket->trip->startPier->name,
                'grade' => $ticket->grade->name,
                'base_price' => $ticket->base_price,
            ];
        });

        $tickets = $current->position()->ordering()
            ->where('terminal_id', $current->terminalId())
            ->with(['grade', 'trip', 'trip.excursion', 'trip.startPier'])
            ->leftJoin('trips', 'trips.id', '=', 'position_ordering_tickets.trip_id')
            ->leftJoin('dictionary_ticket_grades', 'dictionary_ticket_grades.id', '=', 'position_ordering_tickets.grade_id')
            ->orderBy('trips.start_at')
            ->orderBy('dictionary_ticket_grades.order')
            ->select('position_ordering_tickets.*')
            ->get();
        $limits = [];

        $tickets = $tickets->map(function (PositionOrderingTicket $ticket) use (&$limits) {
            $trip = $ticket->trip;
            if (!isset($limits[$trip->id])) {
                $limits[$trip->id] = [
                    'count' => $trip->tickets()->count(),
                    'total' => $trip->tickets_total,
                ];
            }
            return [
                'id' => $ticket->id,
                'trip_id' => $trip->id,
                'trip_start_date' => $trip->start_at->format('d.m.Y'),
                'trip_start_time' => $trip->start_at->format('H:i'),
                'excursion' => $trip->excursion->name,
                'reverse_excursion_id' => $trip->excursion->reverse_excursion_id,
                'backward_price' => $ticket->parent_ticket_id !== null ? $ticket->getBackwardPrice() : null,
                'pier' => $trip->startPier->name,
                'grade' => $ticket->grade->name,
                'base_price' => $price = $ticket->getPrice(),
                'min_price' => $ticket->getMinPrice(),
                'max_price' => $ticket->getMaxPrice(),
                'quantity' => $ticket->quantity,
                'available' => ($price !== null) && $trip->hasStatus(TripSaleStatus::selling, 'sale_status_id') && ($trip->start_at > Carbon::now() || $trip->excursion->is_single_ticket = 1),
            ];
        });

        return APIResponse::response([
            'tickets' => $tickets,
            'limits' => $limits,
            'can_reserve' => $current->partner() ? $current->partner()->profile->can_reserve_tickets : null,

            'has_order' => $order !== null,
            'order_id' => $order->id ?? null,
            'order_external_id' => $order->external_id ?? null,
            'order_status' => $order ? $order->status->name : null,
            'order_total' => $order ? $order->total() : null,
            'order_tickets' => $orderTickets,
            'is_reserve' => $order && ($order->hasStatus(OrderStatus::terminal_creating_from_reserve) || $order->hasStatus(OrderStatus::terminal_wait_for_pay_from_reserve)),
            'actions' => [
                'start_payment' => $order && ($order->hasStatus(OrderStatus::terminal_creating) || $order->hasStatus(OrderStatus::terminal_creating_from_reserve)),
                'cancel_order' => $order && ($order->hasStatus(OrderStatus::terminal_creating) || $order->hasStatus(OrderStatus::terminal_creating_from_reserve)),
                'cancel_payment' => $order && ($order->hasStatus(OrderStatus::terminal_wait_for_pay) || $order->hasStatus(OrderStatus::terminal_wait_for_pay_from_reserve)),
                'print' => $order && $order->hasStatus(OrderStatus::terminal_finishing),
                'finish' => $order && $order->hasStatus(OrderStatus::terminal_finishing),
            ],
            'status' => [
                'creating' => $order === null,
                'created' => $order && ($order->hasStatus(OrderStatus::terminal_creating) || $order->hasStatus(OrderStatus::terminal_creating_from_reserve)),
                'waiting_for_payment' => $order && ($order->hasStatus(OrderStatus::terminal_wait_for_pay) || $order->hasStatus(OrderStatus::terminal_wait_for_pay_from_reserve)),
                'finishing' => $order && $order->hasStatus(OrderStatus::terminal_finishing),
            ],
        ], []);
    }

    /**
     * Add tickets to cart for the selected trip.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function add(Request $request): JsonResponse
    {
        Hit::register(HitSource::terminal);
        $current = Currents::get($request);

        if ($current->position() === null || !$current->isStaffTerminal()) {
            return APIResponse::error('Вы не можете оформлять заказы.');
        }

        if ($current->isStaffTerminal() && Order::query()
                ->where(['terminal_position_id' => $current->positionId(), 'terminal_id' => $current->terminalId()])
                ->whereIn('status_id', OrderStatus::terminal_processing_statuses)
                ->count() > 0
        ) {
            return APIResponse::error('Нельзя добавлять билеты, когда другой заказ уже находится в оформлении.');
        }

        if (
            null === ($tripId = $request->input('trip_id')) ||
            null === ($trip = Trip::query()->where('id', $tripId)->first())
        ) {
            return APIResponse::notFound('Рейс не найден');
        }
        $now = Carbon::now();

        /** @var Trip $trip */

        if (($trip->start_at < $now && $trip->excursion->is_single_ticket=0) || ($rate = $trip->getRate()) === null) {
            return APIResponse::error('Продажа билетов на этот рейс не осуществляется');
        }

        $flat = $request->input('data');
        $data = Arr::undot($flat);

        // make dynamic validation rules and check
        $count = count($data['tickets'] ?? []);
        $rules = [];
        $titles = [];
        for ($i = 0; $i < $count; $i++) {
            $rules["tickets.$i.quantity"] = 'nullable|integer|min:0|bail';
            $titles["tickets.$i.quantity"] = 'Количество';
        }

        if ($errors = $this->validate($data, $rules, $titles)) {
            return APIResponse::validationError($errors);
        }

        // add and update tickets in cart
        $ordering = [];
        $count = 0;

        foreach ($data['tickets'] as $ticket) {
            $grade_id = $ticket['grade_id'];
            $quantity = $ticket['quantity'];
            if ($quantity !== null && $quantity > 0) {
                if ($rate->rates()->where('grade_id', $grade_id)->count() === 0) {
                    return APIResponse::error('Ошибка. Неверные тарифы.');
                }
                /** @var PositionOrderingTicket $ticket */
                $ticket = $current->position()->ordering()
                    ->where(['trip_id' => $trip->id, 'grade_id' => $grade_id, 'terminal_id' => $current->terminalId()])
                    ->firstOrNew(['position_id' => $current->positionId(), 'trip_id' => $trip->id, 'grade_id' => $grade_id, 'terminal_id' => $current->terminalId()]);

                $ticket->quantity += $quantity;
                $count += $quantity;

                $ordering[] = $ticket;
            }
        }

        try {
            DB::transaction(static function () use ($ordering, $count, $trip) {
                // check total quantities
                if ($trip->tickets()->whereIn('status_id', TicketStatus::ticket_countable_statuses)->count() + $count > $trip->tickets_total) {
                    throw new RuntimeException('Недостаточно свободных мест на теплоходе.');
                }
                foreach ($ordering as $ticket) {
                    /** @var PositionOrderingTicket $ticket */
                    $ticket->save();
                }
            });
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success('Билеты добавлены в заказ');
    }

    /**
     * Update tickets quantity in cart.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function quantity(Request $request): JsonResponse
    {
        Hit::register(HitSource::terminal);
        $current = Currents::get($request);

        if ($current->position() === null || !$current->isStaffTerminal()) {
            return APIResponse::error('Вы не можете оформлять заказы.');
        }

        if (Order::query()
                ->where(['terminal_position_id' => $current->positionId(), 'terminal_id' => $current->terminalId()])
                ->whereIn('status_id', OrderStatus::terminal_processing_statuses)
                ->count() > 0
        ) {
            return APIResponse::error('Другой заказ уже находится в оформлении.');
        }

        /** @var PositionOrderingTicket $ticket */
        $ticket = $current->position()->ordering()->with(['trip'])->where(['id' => $request->input('id')])->first();

        $quantity = $request->input('value');

        if ($ticket === null) {
            return APIResponse::error('Билет не найден.');
        }

        $backwardTicket = $ticket->backwardTicket;
        if ($ticket->trip->tickets()->whereIn('status_id', TicketStatus::ticket_countable_statuses)->count() + $quantity - $ticket->quantity > $ticket->trip->tickets_total) {
            throw new RuntimeException('Недостаточно свободных мест на теплоходе.');
        }

        if ($backwardTicket?->trip->tickets()->whereIn('status_id', TicketStatus::ticket_countable_statuses)->count() + $quantity - $ticket->quantity > $ticket->trip->tickets_total) {
            throw new RuntimeException('Недостаточно свободных мест на теплоходе в обратную сторону.');
        }

        if ($backwardTicket) {
            $backwardTicket->quantity = $quantity;
            $backwardTicket->save();
        }

        $ticket->quantity = $quantity;
        $ticket->save();

        return APIResponse::success('Количество билетов обновлено');
    }

    /**
     * Remove ticket from cart.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function remove(Request $request): JsonResponse
    {
        Hit::register(HitSource::terminal);
        $current = Currents::get($request);

        if (!$current->isStaffTerminal()) {
            return APIResponse::error('Вы не можете оформлять заказы.');
        }

        $id = $request->input('ticket_id');

        PositionOrderingTicket::query()->where(['id' => $id, 'position_id' => $current->positionId(), 'terminal_id' => $current->terminalId()])->delete();

        return APIResponse::success('Билет удалён из заказа.');
    }

    /**
     * Remove all ticket from cart.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function clear(Request $request): JsonResponse
    {
        Hit::register(HitSource::terminal);
        $current = Currents::get($request);

        if (!$current->isStaffTerminal()) {
            return APIResponse::error('ВЫ не можете оформлять заказы.');
        }

        PositionOrderingTicket::query()->where(['position_id' => $current->positionId(), 'terminal_id' => $current->terminalId()])->delete();

        return APIResponse::success('Заказ очищен.');
    }
}
