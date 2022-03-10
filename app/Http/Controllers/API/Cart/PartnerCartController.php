<?php

namespace App\Http\Controllers\API\Cart;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Dictionaries\Role;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Positions\PositionOrderingTicket;
use App\Models\Sails\Trip;
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
        $current = Currents::get($request);

        if ((null === ($position = $current->position())) || ($current->partner() === null)) {
            return APIResponse::error('Вы не можете оформлять заказы.');
        }

        $tickets = $position->ordering()
            ->whereNull('terminal_id')
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
                'pier' => $trip->startPier->name,
                'grade' => $ticket->grade->name,
                'base_price' => $price = $ticket->getPrice(),
                'min_price' => $ticket->getMinPrice(),
                'max_price' => $ticket->getMaxPrice(),
                'quantity' => $ticket->quantity,
                'available' => ($price !== null) && $trip->hasStatus(TripSaleStatus::selling, 'sale_status_id') && ($trip->start_at > Carbon::now()),
            ];
        });

        return APIResponse::response([
            'tickets' => $tickets,
            'limits' => $limits,
            'can_reserve' => $current->partner() ? $current->partner()->profile->can_reserve_tickets : null,
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
        $current = Currents::get($request);

        if ((null === ($position = $current->position())) || ($current->partner() === null)) {
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

        if ($trip->start_at < $now || ($rate = $trip->getRate()) === null) {
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
            return APIResponse::formError($flat, $rules, $titles, $errors);
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
                $ticket = $position->ordering()
                    ->where(['trip_id' => $trip->id, 'grade_id' => $grade_id, 'terminal_id' => $current->terminalId()])
                    ->firstOrNew(['position_id' => $position->id, 'trip_id' => $trip->id, 'grade_id' => $grade_id, 'terminal_id' => $current->terminalId()]);

                $ticket->quantity += $quantity;
                $count += $quantity;

                $ordering[] = $ticket;
            }
        }

        try {
            DB::transaction(static function () use ($ordering, $count, $trip) {
                // check total quantities
                if ($trip->tickets()->count() + $count > $trip->tickets_total) {
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

        return APIResponse::formSuccess('Билеты добавлены в заказ');
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
        $current = Currents::get($request);

        if ((null === ($position = $current->position())) || ($current->partner() === null)) {
            return APIResponse::error('ВЫ не можете оформлять заказы.');
        }

        $id = $request->input('ticket_id');

        PositionOrderingTicket::query()->where(['id' => $id, 'position_id' => $position->id, 'terminal_id' => $current->terminalId()])->delete();

        return APIResponse::formSuccess('Билет удалён из заказа.');
    }
}
