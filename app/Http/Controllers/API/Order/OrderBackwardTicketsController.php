<?php

namespace App\Http\Controllers\API\Order;

use App\Helpers\PriceConverter;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Hit\Hit;
use App\Models\Positions\PositionOrderingTicket;
use App\Models\Sails\Trip;
use App\Models\User\Helpers\Currents;
use App\Services\NevaTravel\GetNevaComboPriceAction;
use App\Services\NevaTravel\NevaTravelRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderBackwardTicketsController extends ApiController
{

    public function getBackwardTrips(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);

        $tripID = $request->input('tripId');
        $trip = Trip::find($tripID);

        $ticket = PositionOrderingTicket::find($request->input('ticketId'));

        if ($ticket?->backwardTicket) {
            return APIResponse::error('У этого билета уже есть обратный билет');
        }
        $backwardExcursionID = $trip->excursion->reverse_excursion_id;
        if (!$backwardExcursionID) {
            return ApiResponse::error('Не назначена экскурсия для обратного билета');
        }


        $startAt = Carbon::parse($trip->start_at);

        $trips = Trip::query()
            ->withCount(['tickets' => function (Builder $query) {
                $query->whereIn('status_id', TicketStatus::ticket_countable_statuses);
            }])
            ->where('excursion_id', $backwardExcursionID)
            ->whereDate('start_at', $startAt)
            ->where('start_at', '>=', $trip->end_at)
            ->where('status_id', TripStatus::regular)
            ->where('sale_status_id', TripSaleStatus::selling)
            ->whereHas('excursion.ratesLists', function (Builder $query) use ($startAt) {
                $query->whereDate('start_at', '<=', $startAt)->whereDate('end_at', '>=', $startAt);
            })
            ->get();

        $trips = $trips->filter(function (Trip $trip) {
            return $trip->tickets_total >= $trip->getAttribute('tickets_count') + 1;
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


    public function addBackwardTickets(Request $request)
    {
        $current = Currents::get($request);

        $trip = Trip::where('id', $request->input('back_trip_id'))->first();

        if (!$trip){
            return APIResponse::notFound('Рейс не найден');
        }

        $requestWithOneTicket = $request->input('ticketId');
        $ticketIds = collect($request->input('tickets'))->pluck('id')->toArray();
        $ticketIds[] = $requestWithOneTicket;

        $tickets = $current->position()->ordering()->get();
        $ticketsHasBackwards = $tickets->filter(fn (PositionOrderingTicket $ticket) => $ticket->parent_ticket_id);
        if ($ticketsHasBackwards->isNotEmpty()){
            return APIResponse::error('У этого билета уже есть обратный билет');
        }

        if ($trip->provider_id === Provider::neva_travel) {
            $straightTrip = Trip::find($tickets[0]->trip_id);
            $nevaBackwardPrices = (new GetNevaComboPriceAction())->run($straightTrip, $trip);
        }

        foreach (array_filter($ticketIds) as $ticketId) {

            $ticketFromCart = PositionOrderingTicket::where('id', $ticketId)->first();
            if (!empty($nevaBackwardPrices)){
                $nevaBackwardPrice = collect($nevaBackwardPrices)->filter(function ($price) use ($ticketFromCart) {
                    return $price['grade_id'] == $ticketFromCart->grade_id;
                });
            }

            $current->position()
                ->ordering()
                ->create([
                    'position_id' => $current->positionId() ?? null,
                    'terminal_id' => $current->terminalId() ?? null,
                    'trip_id' => $trip->id,
                    'grade_id' => $ticketFromCart->grade_id,
                    'parent_ticket_id' => $ticketFromCart->id,
                    'quantity' => $ticketFromCart->quantity,
                    'base_price' => isset($nevaBackwardPrice) ? $nevaBackwardPrice->first()['base_price'] - $ticketFromCart->base_price : null,
                ]);
        }

        return APIResponse::success('Билет добавлен в заказ');
    }


}
