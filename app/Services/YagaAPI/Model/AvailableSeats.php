<?php

namespace App\Services\YagaAPI\Model;

use App\Models\Dictionaries\TicketStatus;
use App\Models\Sails\Trip;

class AvailableSeats
{

    public static function getResource(Trip $trip): array
    {
        $rates = $trip->excursion->rateForDate($trip->start_at)->rates->filter(fn($rate) => $rate->partner_price > 0);
        $grades = $rates->map(fn($rate) => $rate->grade);
        $ticketsCount = $trip->tickets_total - $trip->tickets->filter(fn($tickets) => $tickets->whereIn('status_id', TicketStatus::ticket_countable_statuses))->count();

        return [
            "gatewayLevelStates" => (new Level($grades))->getResourceWithSeatsCount($ticketsCount),
            "seatCategories" => SeatCategory::getResource($rates)
        ];
    }
}
