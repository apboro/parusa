<?php

namespace App\Http\Controllers\API\Cart;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
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

class TicketsAddController extends ApiEditController
{
    /**
     * Add tickets to cart.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function add(Request $request): JsonResponse
    {
        $current = Currents::get($request);

        if ($current->isStaff()) {
            return APIResponse::error('Сотрудники не могут оформлять заказы.');
        }

        if (($position = $current->position()) === null || $current->partner() === null) {
            return APIResponse::error('Вы не являетесь представителем компании-партнёра.');
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
                    ->where(['trip_id' => $trip->id, 'grade_id' => $grade_id])
                    ->firstOrNew(['position_id' => $position->id, 'trip_id' => $trip->id, 'grade_id' => $grade_id]);

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
}
