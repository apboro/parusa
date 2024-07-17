<?php

namespace App\Services\YagaAPI15\Model;

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
            "hallId" => $trip->start_pier_id,
            "id" => $trip->id,
            "organizerId" => Organizer::getStaticResource()['id'],
            "saleCanceling" => $trip->start_at->subHour()->toIso8601ZuluString(),
            "saleClosing" => $trip->start_at->subMinutes(5)->toIso8601ZuluString(),
            "saleOpening" => $trip->start_at->subMonth()->toIso8601ZuluString(),
            "saleSupported" => true,
            "sessionTime" => [
                "sessionEnd" => $trip->end_at->toIso8601ZuluString(),
                "sessionStart" => $trip->start_at->toIso8601ZuluString(),
                "timezone" => 'Europe/Moscow',
                "type" => 'ON_TIME'
            ],
            "tags" => [
                [
                    "name" => 'Теплоходная экскурсия',
                    "type" => 'описание сеанса'
                ]
            ],
            "venueId" => $trip->start_pier_id,
        ];
    }

}


