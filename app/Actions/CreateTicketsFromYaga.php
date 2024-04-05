<?php

namespace App\Actions;

use App\Models\Dictionaries\TicketStatus;
use App\Models\Sails\Trip;
use App\Models\Tickets\Ticket;
use App\Models\Tickets\TicketRate;

class CreateTicketsFromYaga
{

    public function __construct(protected array $data, protected Trip $trip, protected TicketRate $rate)
    {
    }

    public function execute(): array
    {
        foreach ($this->data['items'] as $item) {

            $tickets[] = Ticket::make([
                'trip_id' => $this->data['sessionId'],
                'grade_id' => $item['categoryId'],
                'status_id' => TicketStatus::yaga_reserved,
                'provider_id' => $this->trip->excursion->provider_id,
                'base_price' => $this->rate->partner_price,
            ]);
        }
        return $tickets ?? [];
    }
}
