<?php

namespace App\Http\Controllers\API\Order;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Order\Order;
use App\Models\Sails\Trip;
use App\Models\Sails\TripChain;
use App\Models\Tickets\Ticket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderTransferController extends ApiController
{
    /**
     * Get transfer trips.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function transfer(Request $request): JsonResponse
    {
        $query = Order::query()
            ->where('id', $request->input('id'))
            ->with(['tickets', 'tickets.trip', 'tickets.trip.excursion']);
        $order = $query->first();

        $excursionIDs = [];
        if (!empty($request->input('transfers'))) {
            $queryTickets = Ticket::query()
                ->whereIn('id', $request->input('transfers'))
                ->with('trip.excursion')
                ->get();
            $excursionIDs = $queryTickets->pluck('trip')
                ->flatten()->pluck('excursion')
                ->flatten()->pluck('id')->unique()
                ->toArray();
        }
        $countTickets = count($request->input('transfers'));

        $tickets = $order->tickets->map(function (Ticket $ticket) use ($excursionIDs) {
            return [
                'id' => $ticket->id,
                'base_price' => $ticket->base_price,
                'trip_id' => $ticket->trip_id,
                'trip_start_date' => $ticket->trip->start_at->format('d.m.Y'),
                'trip_start_time' => $ticket->trip->start_at->format('H:i'),
                'excursion' => $ticket->trip->excursion->name,
                'excursion_id' => $ticket->trip->excursion->id,
                'transferable' => in_array($ticket->status_id, TicketStatus::ticket_paid_statuses, true),
                'pier' => $ticket->trip->startPier->name,
                'grade' => $ticket->grade->name,
                'status' => $ticket->status->name,
                'returnable' => in_array($ticket->status_id, TicketStatus::ticket_returnable_statuses, true),
                'disabled' => !empty($excursionIDs) && !in_array($ticket->trip->excursion->id, $excursionIDs),
            ];
        });


        $currentDate = Carbon::now();

        $trips = Trip::query()
            ->withCount(['tickets' => function(Builder $query) {
                $query->whereIn('status_id', TicketStatus::ticket_countable_statuses);
            }])
            ->whereIn('excursion_id', $excursionIDs)
            ->whereDate('start_at', '>=', $currentDate)
            ->where('status_id', TripStatus::regular)
            ->where('sale_status_id', TripSaleStatus::selling)
            ->whereHas('excursion.ratesLists', function (Builder $query) use ($currentDate) {
                $query->whereDate('start_at', '<=', $currentDate)->whereDate('end_at', '>=', $currentDate);
            })
            ->get();

        $trips = $trips->filter(function (Trip $trip) use ($countTickets) {
            return $trip->tickets_total >= $trip->getAttribute('tickets_count') + $countTickets;
        });

        $tripsDates = $trips->sortBy('start_at')->pluck('start_at');
        /** @var LengthAwarePaginator $trips */
        $tripsDates = $tripsDates->map(function ($date) {
            return Carbon::parse($date)->format('d.m.Y');
        })->toArray();
        $dates = array_unique($tripsDates);

        return APIResponse::response([
            'tickets' => $tickets,
            'dates' => $dates,
        ]);
    }

    /**
     * Transfer tickets to trip.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        if (empty($request->input('transfers'))) {
            return APIResponse::error('Выберите билеты');
        }

        if (empty($request->input('tripId'))) {
            return APIResponse::error('Выберите рейс');
        }

        $tickets = Ticket::query()
            ->whereIn('id', $request->input('transfers'))
            ->get();

        $currentDate = Carbon::now();
        $trip = Trip::query()
            ->where([
                'id' => $request->input('tripId'),
                'status_id' => TripStatus::regular,
                'sale_status_id' => TripSaleStatus::selling
            ])
            ->withCount(['tickets' => function(Builder $query) {
                $query->whereIn('status_id', TicketStatus::ticket_countable_statuses);
            }])
            ->whereDate('start_at', '>=', $currentDate)
            ->whereHas('excursion.ratesLists', function (Builder $query) use ($currentDate) {
                $query->whereDate('start_at', '<=', $currentDate)->whereDate('end_at', '>=', $currentDate);
            })
            ->first();

        if (empty($trip)) {
            return APIResponse::error('Рейс не найден или недоступен');
        }

        if ($trip->tickets_total < $trip->getAttribute('tickets_count') + count($tickets)) {
            return APIResponse::error('Нехватает мест в рейсе');
        }

        foreach ($tickets as $ticket) {
            $ticket->update([
                'trip_id' => $trip->id
            ]);
        }

        return APIResponse::success('Билеты успешно перенесены.');
    }

    public function trips(Request $request): JsonResponse
    {
        $excursionIDs = [];
        if (empty($request->input('date'))) {
            return APIResponse::error('Выберите дату');
        }

        if (!empty($request->input('transfers'))) {
            $queryTickets = Ticket::query()
                ->whereIn('id', $request->input('transfers'))
                ->with('trip.excursion')
                ->get();
            $excursionIDs = $queryTickets->pluck('trip')
                ->flatten()->pluck('excursion')
                ->flatten()->pluck('id')->unique()
                ->toArray();
        }
        $countTickets = count($request->input('transfers'));

        $startDate = Carbon::parse($request->input('date'));
        $trips = Trip::query()
            ->withCount(['tickets' => function(Builder $query) {
                $query->whereIn('status_id', TicketStatus::ticket_countable_statuses);
            }])
            ->whereIn('excursion_id', $excursionIDs)
            ->whereDate('start_at',  $startDate)
            ->where('status_id', TripStatus::regular)
            ->where('sale_status_id', TripSaleStatus::selling)
            ->get();

        $trips = $trips->filter(function (Trip $trip) use ($countTickets) {
            return $trip->tickets_total >= $trip->getAttribute('tickets_count') + $countTickets;
        });

        /** @var LengthAwarePaginator $trips */
        $trips->transform(function (Trip $trip) {
            /** @var TripChain $chain */
            $chain = $trip->chains->first();
            $chainStart = $chain ? $chain->trips()->min('start_at') : null;
            $chainEnd = $chain ? $chain->trips()->max('start_at') : null;

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
                'chained' => $trip->getAttribute('chains_count') > 0,
                'chain_trips_count' => $chain ? $chain->getAttribute('trips_count') : null,
                'chain_trips_start_at' => $chainStart ? Carbon::parse($chainStart)->format('d.m.Y') : null,
                'chain_trips_end_at' => $chainEnd ? Carbon::parse($chainEnd)->format('d.m.Y') : null,
                '_trip_start_at' => $trip->start_at->format('Y-m-d'),
                '_chain_end_at' => $chainEnd ? Carbon::parse($chainEnd)->format('Y-m-d') : null,
            ];
        });

        return APIResponse::response([
            'trips' => $trips
        ]);
    }
}
