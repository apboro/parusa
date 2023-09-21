<?php

namespace App\Http\Controllers\API\Order;

use App\Classes\EmailReceiver;
use App\Events\CityTourOrderPaidEvent;
use App\Events\NevaTravelCancelOrderEvent;
use App\Events\NevaTravelOrderPaidEvent;
use App\Events\NewCityTourOrderEvent;
use App\Events\NewNevaTravelOrderEvent;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Hit\Hit;
use App\Models\Order\Order;
use App\Models\Sails\Trip;
use App\Models\Tickets\Ticket;
use App\Notifications\OrderNotification;
use App\Services\CityTourBus\CityTourOrder;
use App\Services\NevaTravel\NevaOrder;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use RuntimeException;

class OrderTicketReplacementController extends ApiController
{
    /**
     * Get available dates to replace tickets.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function getAvailableDates(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $excursionID = $request->input('excursion_id');

        $now = Carbon::now();

        /** @var Collection $tripsDates */
        $tripsDates = Trip::query()
            ->where('excursion_id', $excursionID)
            ->whereDate('start_at', '>=', $now)
            ->where('status_id', TripStatus::regular)
            ->where('sale_status_id', TripSaleStatus::selling)
            ->whereHas('excursion.ratesLists', function (Builder $query) use ($now) {
                $query->whereDate('start_at', '<=', $now)->whereDate('end_at', '>=', $now);
            })
            ->orderBy('start_at')
            ->select(DB::raw('DATE(start_at) start_at'))
            ->groupBy(DB::RAW('DATE(start_at)'))
            ->pluck('start_at');

        return APIResponse::response([
            'dates' => $tripsDates->map(function (Carbon $date) {
                return $date->format('Y-m-d');
            }),
        ]);
    }

    /**
     * Replace tickets to trip.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function replaceTickets(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $orderId = $request->input('order_id');
        $tripId = $request->input('trip_id');
        $tickets = $request->input('tickets');

        if (!isset($orderId, $tripId, $tickets)) {
            return APIResponse::error('Выберите билеты и рейс для замены');
        }

        $tickets = Ticket::query()
            ->where('order_id', $orderId)
            ->whereIn('id', $tickets)
            ->get();

        /** @var Trip|null $trip */
        $trip = Trip::query()
            ->withCount(['tickets' => function (Builder $query) {
                $query->whereIn('status_id', TicketStatus::ticket_countable_statuses);
            }])
            ->where('id', $tripId)
            ->where('status_id', TripStatus::regular)
            ->where('sale_status_id', TripSaleStatus::selling)
            ->whereHas('excursion.ratesLists', function (Builder $query) {
                $query
                    ->whereRaw('DATE(tickets_rates_list.start_at) <= DATE(trips.start_at)')
                    ->whereRaw('DATE(tickets_rates_list.end_at) >= DATE(trips.end_at)')
                    ->whereHas('rates', function (Builder $query) {
                        $query->where('base_price', '>', 0);
                    });
            })
            ->first();

        if ($trip === null) {
            return APIResponse::error('Рейс не найден или недоступен');
        }

        if ($trip->tickets_total < $trip->getAttribute('tickets_count') + count($tickets)) {
            return APIResponse::error('Нехватает мест в рейсе');
        }

        /** @var Order $order */
        $order = Order::query()->where('id', $orderId)->first();

        try {
            DB::transaction(static function () use ($trip, $tickets, $order) {
                foreach ($tickets as $ticket) {
                    /** @var Ticket $ticket */
                    if (!in_array($ticket->status_id, TicketStatus::ticket_paid_statuses)) {
                        throw new RuntimeException('Один или несколько билетов имеет статус недоступный для обмена');
                    }
                    $ticket->trip_id = $trip->id;
                    $ticket->save();
                }

                NevaTravelCancelOrderEvent::dispatch($order);
                NewNevaTravelOrderEvent::dispatch($order);
                NevaTravelOrderPaidEvent::dispatch($order);
                try {
                    if ($order->email)
                        Notification::sendNow(new EmailReceiver($order->email, $order->name), new OrderNotification($order));
                } catch (Exception $exception) {
                    Log::channel('tickets_sending')->error(sprintf("Error order [%s] sending tickets [%s]: %s", $order->id, $order->email, $exception->getMessage()));
                }

            });
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success('Билеты успешно перенесены.');
    }

    /**
     * Get trips list for excursion by date.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getTripsForDate(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $excursionID = $request->input('excursion_id');
        $date = $request->input('date');
        $count = $request->input('count');

        if (!isset($excursionID, $date, $count)) {
            return APIResponse::error('Выберите билеты для замены и дату');
        }

        $startAt = Carbon::parse($date);

        $trips = Trip::query()
            ->withCount(['tickets' => function (Builder $query) {
                $query->whereIn('status_id', TicketStatus::ticket_countable_statuses);
            }])
            ->where('excursion_id', $excursionID)
            ->whereDate('start_at', $startAt)
            ->where('status_id', TripStatus::regular)
            ->where('sale_status_id', TripSaleStatus::selling)
            ->whereHas('excursion.ratesLists', function (Builder $query) use ($startAt) {
                $query->whereDate('start_at', '<=', $startAt)->whereDate('end_at', '>=', $startAt);
            })
            ->get();

        $trips = $trips->filter(function (Trip $trip) use ($count) {
            return $trip->tickets_total >= $trip->getAttribute('tickets_count') + $count;
        })->sortBy('start_at')->values();

        /** @var LengthAwarePaginator $trips */
        $trips->transform(function (Trip $trip) {
            return [
                'id' => $trip->id,
                'start_date' => $trip->start_at->format('d.m.Y'),
                'start_time' => $trip->start_at->format('H:i'),
                'excursion' => $trip->excursion->name,
                'pier' => $trip->startPier->name,
                'ship' => $trip->ship->name,
                'tickets_count' => $trip->getAttribute('tickets_count'),
                'tickets_total' => $trip->tickets_total,
                'status' => $trip->status->name,
                'status_id' => $trip->status_id,
                'sale_status' => $trip->saleStatus->name,
                'has_rate' => $trip->hasRate(),
                'sale_status_id' => $trip->sale_status_id,
                '_trip_start_at' => $trip->start_at->format('Y-m-d'),
            ];
        });

        return APIResponse::response(['trips' => $trips]);
    }
}
