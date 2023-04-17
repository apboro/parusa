<?php

namespace App\Http\Controllers\API\Order;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\TicketStatus;
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
     * Remove reserve.
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
            ->get();

        $tripsDates = $trips->pluck('start_at');
        $tripsDates = $tripsDates->map(function ($date) {
            return Carbon::parse($date)->format('d.m.Y');
        })->toArray();
        $dates = array_unique($tripsDates);

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
            'tickets' => $tickets,
            'dates' => $dates,
            'trips' => $trips
        ]);
    }
}
