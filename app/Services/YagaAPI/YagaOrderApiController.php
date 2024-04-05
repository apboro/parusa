<?php

namespace App\Services\YagaAPI;

use App\Actions\CreateOrderFromYaga;
use App\Actions\CreateTicketsFromYaga;
use App\Http\APIResponse;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Sails\Trip;
use App\Models\Tickets\Ticket;
use App\Services\YagaAPI\Model\AvailableSeats;
use App\Services\YagaAPI\Requests\Order\AvailableSeatsRequest;
use App\Services\YagaAPI\Requests\Order\ReserveTicketsRequest;
use Illuminate\Http\JsonResponse;

class YagaOrderApiController
{
    public function availableSeats(AvailableSeatsRequest $request): JsonResponse
    {
        $data = $request->all();

        if (!$data['sessionId']) {
            return response()->json();
        }

        $trip = Trip::with(['excursion', 'excursion.ratesLists', 'tickets'])->where('id', $data['sessionId'])->first();

        if (!$trip) {
            return response()->json();
        }

        if (!empty($data['venueId']) && $trip->ship->id != $data['venueId']) {
            return response()->json();
        }
        if (!empty($data['eventId']) && $trip->excursion->id != $data['eventId']) {
            return response()->json();
        }
        if (!empty($data['hallId']) && $trip->ship->id != $data['hallId']) {
            return response()->json();
        }
        if (!empty($data['sessionTime']) && $trip->start_at->timestamp != $data['sessionTime']) {
            return response()->json();
        }

        $results = AvailableSeats::getResource($trip);

        return response()->json([$results]);
    }


    public function reserve(ReserveTicketsRequest $request): JsonResponse
    {
        $data = $request->all();

        if (!$data['sessionId']) {
            return response()->json('В запросе отсутствует sessionId');
        }

        $trip = Trip::with('excursion')
            ->where('id', $data['sessionId'])
            ->first();

        if (!$trip) {
            return response()->json('Рейс не найден');
        }

        if (!empty($data['venueId']) && $trip->ship->id != $data['venueId']) {
            return response()->json();
        }
        if (!empty($data['eventId']) && $trip->excursion->id != $data['eventId']) {
            return response()->json();
        }
        if (!empty($data['hallId']) && $trip->ship->id != $data['hallId']) {
            return response()->json();
        }
        if (!empty($data['sessionTime']) && $trip->start_at->timestamp != $data['sessionTime']) {
            return response()->json();
        }

        $rateList = $trip->getRate();
        if (($trip->start_at < now() && $trip->excursion->is_single_ticket == 0)
            || ($trip->excursion->is_single_ticket != 0 && $trip->start_at < now() && !$trip->start_at->isCurrentDay())
            || !$rateList
            || !$trip->hasStatus(TripStatus::regular)
            || !$trip->hasStatus(TripSaleStatus::selling, 'sale_status_id')
        ) {
            return response()->json('Рейс не актуален');
        }

        if (empty($data['items'])) {
            return response()->json('В запросе нет билетов');
        }

        if (!$this->areEnoughTicketsAvailable($trip, $data)) {
            return response()->json('Недостаточно билетов');
        }

        foreach ($data['items'] as $ticket) {
            $rate = $rateList->rates()->where('grade_id', $ticket['categoryId'])->first();
            if (!$rate) {
                return response()->json('Неправильно указана категория билета - ' . $ticket['categoryId']);
            }

            if (!$rate->partner_price) {
                return response()->json('Для данной категории билета не установлена цена');
            }
        }

        $newTickets = (new CreateTicketsFromYaga($data, $trip, $rate))->execute();

        if (empty($newTickets)) {
            return response()->json('Не удалось оформить билеты');
        }

        $order = (new CreateOrderFromYaga($data, $newTickets))->execute();

        return response()->json($order);
    }

    public function orderInfo()
    {
    }

    public function orderStatus()
    {
    }

    public function cancelOrder()
    {
    }

    public function checkPromocode()
    {
    }

    public function clearReservation()
    {
    }

    public function approve()
    {
    }

    private function areEnoughTicketsAvailable($trip, $data): bool
    {
        $ticketsLeft = $trip->tickets()
            ->whereIn('status_id', TicketStatus::ticket_countable_statuses)
            ->count();
        $ticketsNeed = count($data['items']);

        return ($ticketsLeft + $ticketsNeed) <= $trip->tickets_total;
    }

}
