<?php

namespace App\Services\YagaAPI\Model;

use App\Models\Dictionaries\TicketStatus;
use App\Models\Sails\Trip;

class Session
{
    public function getResource(Trip $trip): array
    {
        return [
            "availableSeatCount" => $trip->tickets_total - $trip->tickets->filter(fn ($tickets) => $tickets->whereIn('status_id', TicketStatus::ticket_countable_statuses))->count(),
            "canPreOrder" => 'PRE_ORDER_ALLOWED',
            "cancelAllowance" => 'CANCEL_ALLOWED',
            "eventId" => $trip->excursion->id,
            "hallId" => $trip->ship->id,
            "id" => $trip->id,
            "organizerId" => (new Organizer())->getResource($trip->excursion),
            "saleCanceling" => $trip->start_at->subHour()->getTimestamp(),
            "saleClosing" => $trip->start_at->subMinutes(5)->getTimestamp(),
            "saleOpening" => $trip->start_at->subDay()->getTimestamp(),
            "saleSupported" => true,
            "sessionTime" => [
                "sessionEnd" => $trip->end_at->getTimestamp(),
                "sessionStart" => $trip->start_at->getTimestamp(),
                "timezone" => 'Europe/Moscow',
                "type" => 'ON_TIME'
            ],
            "tags" => [
                [
                    "name" => 'Теплоходная экскурсия',
                    "type" => 'описание сеанса'
                ]
            ],
            "venueId" => $trip->ship->id,
        ];
    }

}


