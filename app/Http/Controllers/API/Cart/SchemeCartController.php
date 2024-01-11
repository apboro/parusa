<?php

namespace App\Http\Controllers\API\Cart;

use App\Http\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\SeatStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Hit\Hit;
use App\Models\Positions\PositionOrderingTicket;
use App\Models\Sails\Trip;
use App\Models\Ships\Seats\TripSeat;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SchemeCartController extends Controller
{
    public function add(Request $request)
    {
        Hit::register(HitSource::partner);
        $current = Currents::get($request);

        if ($current->position() === null && !$current->isRepresentative() && $current->partner() === null) {
            return APIResponse::error('Вы не можете оформлять заказы.');
        }

        if (
            null === ($tripId = $request->input('trip_id')) ||
            null === ($trip = Trip::query()->where('id', $tripId)->first())
        ) {
            return APIResponse::notFound('Рейс не найден');
        }

        if ($trip->provider_id !== null && $current->position()->ordering()->exists()) {
            return APIResponse::error('Заказы данного поставщика должны оформляться отдельно, очистите корзину.');
        }

        $existingTickets = $current->position()->ordering()->get();
        $hasExternalTickets = $existingTickets->filter(function ($ticket) {
            return $ticket->trip->provider_id !== null;
        })->isNotEmpty();

        if ($hasExternalTickets) {
            return APIResponse::error('В корзине содержатся билеты другого поставщика, очистите корзину.');
        }

        $now = Carbon::now();

        /** @var Trip $trip */

        if (($trip->start_at < $now && $trip->excursion->is_single_ticket = 0) || ($rate = $trip->getRate()) === null) {
            return APIResponse::error('Продажа билетов на этот рейс не осуществляется');
        }

        $ordering = [];
        foreach ($request->tickets as $ticket) {
            if ($rate->rates()->where('grade_id', $ticket['grade']['id'])->count() === 0) {
                return APIResponse::error('Ошибка. Неверные тарифы.');
            }
            $ticket = $current->position()->ordering()
                ->firstOrNew([
                    'position_id' => $current->positionId(),
                    'trip_id' => $trip->id,
                    'quantity' => 1,
                    'seat_id' => $ticket['seatId'],
                    'grade_id' => $ticket['grade']['id'],
                    'terminal_id' => $current->terminalId(),
                    'menu_id' => $ticket['menu']['id'] ?? null
            ]);
            $ordering[] = $ticket;
        }
        try {
            DB::transaction(static function () use ($ordering, $trip) {
                // check total quantities
                if ($trip->tickets()->whereIn('status_id', TicketStatus::ticket_countable_statuses)->count() + count($ordering) > $trip->tickets_total) {
                    throw new RuntimeException('Недостаточно свободных мест на теплоходе.');
                }
                foreach ($ordering as $ticket) {
                    /** @var PositionOrderingTicket $ticket */
                    $ticket->save();
                    TripSeat::query()->updateOrCreate([
                        'trip_id' => $trip->id,
                        'seat_id' => $ticket->seat->id],
                        ['status_id' => SeatStatus::reserve]);
                }
            });
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success('Билеты добавлены в заказ');
    }

    public function reserveSeat(Request $request)
    {
        TripSeat::query()
            ->updateOrCreate(['trip_id' => $request->tripId,
                'seat_id' => $request->seatId],
                ['status_id' => SeatStatus::reserve]);

        return APIResponse::success('updated');
    }
}
