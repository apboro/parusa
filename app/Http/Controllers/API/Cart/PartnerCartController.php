<?php

namespace App\Http\Controllers\API\Cart;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\PartnerType;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Hit\Hit;
use App\Models\Order\Order;
use App\Models\Positions\PositionOrderingTicket;
use App\Models\Sails\Trip;
use App\Models\Ships\Seats\TripSeat;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PartnerCartController extends ApiEditController
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
        Hit::register(HitSource::partner);
        $current = Currents::get($request);

        if ($current->position() === null || !$current->isRepresentative()) {
            return APIResponse::error('Вы не можете оформлять заказы.');
        }

        $tickets = $current->position()->ordering()
            ->whereNull('terminal_id')
            ->with(['grade', 'trip', 'trip.excursion', 'trip.startPier'])
            ->leftJoin('trips', 'trips.id', '=', 'position_ordering_tickets.trip_id')
            ->leftJoin('dictionary_ticket_grades', 'dictionary_ticket_grades.id', '=', 'position_ordering_tickets.grade_id')
            ->orderBy('trips.start_at')
            ->orderBy('dictionary_ticket_grades.order')
            ->select('position_ordering_tickets.*')
            ->get();
        $limits = [];
        $ticketTrip = $tickets->first()?->trip->id;
        $hasProviderTicket = $tickets->filter(function ($ticket) {
            return $ticket->trip->provider_id !== null;
        })->isNotEmpty();

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
                'ticket_provider_id' => $ticket->trip->provider_id,
                'trip_start_date' => $trip->start_at->format('d.m.Y'),
                'trip_start_time' => $trip->start_at->format('H:i'),
                'excursion' => $trip->excursion->name,
                'is_single_ticket' => $trip->excursion->is_single_ticket,
                'reverse_excursion_id' => $trip->excursion->reverse_excursion_id,
                'pier' => $trip->startPier->name,
                'grade' => $ticket->grade->name,
                'seat' => $ticket->seat,
                'base_price' => $price = $ticket->getPartnerPrice() ?? $ticket->getPrice(),
                'min_price' => $ticket->getMinPrice(),
                'max_price' => $ticket->getMaxPrice(),
                'backward_price' => $ticket->parent_ticket_id !== null ? $ticket->getBackwardPrice() : null,
                'quantity' => $ticket->quantity,
                'available' => ($price !== null) && $trip->hasStatus(TripSaleStatus::selling, 'sale_status_id') && ($trip->start_at > Carbon::now() || $trip->excursion->is_single_ticket = 1),
            ];
        });

        return APIResponse::response([
            'ticketTrip' => $ticketTrip,
            'hasProviderTickets' => $hasProviderTicket,
            'openshift' => $current->partner()?->type_id === PartnerType::promoter ? $current->partner()->getOpenedShift() : null,
            'tickets' => $tickets,
            'limits' => $limits,
            'can_reserve' => $current->partner() ? ($current->partner()->profile->can_reserve_tickets && !$hasProviderTicket) : null,
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
        Hit::register(HitSource::partner);
        $current = Currents::get($request);

        if ($current->position() === null || !$current->isRepresentative()) {
            return APIResponse::error('Вы не можете оформлять заказы.');
        }

        if (
            null === ($tripId = $request->input('trip_id')) ||
            null === ($trip = Trip::query()->where('id', $tripId)->first())
        ) {
            return APIResponse::notFound('Рейс не найден');
        }

        $now = Carbon::now();

        /** @var Trip $trip */

        if (($trip->start_at < $now && $trip->excursion->is_single_ticket = 0) || ($rate = $trip->getRate()) === null) {
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
                    ->where([
                        'trip_id' => $trip->id,
                        'grade_id' => $grade_id,
                        'terminal_id' => $current->terminalId()])
                    ->firstOrNew([
                        'position_id' => $current->positionId(),
                        'trip_id' => $trip->id,
                        'grade_id' => $grade_id,
                        'terminal_id' => $current->terminalId()]);

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
        Hit::register(HitSource::partner);
        $current = Currents::get($request);

        if ($current->position() === null || !$current->isRepresentative()) {
            return APIResponse::error('Вы не можете оформлять заказы.');
        }

        if (Order::query()
                ->where(['position_id' => $current->positionId(), 'terminal_id' => null])
                ->whereIn('status_id', OrderStatus::terminal_processing_statuses)
                ->count() > 0
        ) {
            return APIResponse::error('Другой заказ уже находится в оформлении.');
        }

        /** @var PositionOrderingTicket $ticket */
        $ticket = $current->position()->ordering()->with(['trip'])->where(['id' => $request->input('id')])->first();

        $quantity = $request->input('value');

        if ($ticket->parent_ticket_id) {
            $parentTicket = $current->position()->ordering()->where('id', $ticket->parent_ticket_id)->first();
            if ($quantity > $parentTicket->quantity) {
                return APIResponse::error('Обратных билетов не может быть больше, чем прямых.');
            }
        }

        if ($ticket === null) {
            return APIResponse::error('Билет не найден.');
        }
        /* @var PositionOrderingTicket $backwardTicket * */
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
        Hit::register(HitSource::partner);
        $current = Currents::get($request);

        if ((null === ($position = $current->position())) || ($current->partner() === null)) {
            return APIResponse::error('ВЫ не можете оформлять заказы.');
        }

        $id = $request->input('ticket_id');


        $cartTicketQuery = PositionOrderingTicket::query()->where(['id' => $id, 'position_id' => $position->id, 'terminal_id' => $current->terminalId()]);
        $ticket = $cartTicketQuery->first();
        TripSeat::query()->where('trip_id', $ticket->trip_id)->where('seat_id', $ticket->seat_id)->delete();

        $cartTicketQuery->delete();

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
        Hit::register(HitSource::partner);
        $current = Currents::get($request);

        if ((null === ($position = $current->position())) || ($current->partner() === null)) {
            return APIResponse::error('Вы не можете оформлять заказы.');
        }

        $cartTickets = PositionOrderingTicket::query()->where(['position_id' => $position->id, 'terminal_id' => $current->terminalId()]);
        foreach ($cartTickets->get() as $ticket) {
            TripSeat::query()->where('trip_id', $ticket->trip_id)->delete();
        }
        $cartTickets->delete();

        return APIResponse::success('Заказ очищен.');
    }
}
