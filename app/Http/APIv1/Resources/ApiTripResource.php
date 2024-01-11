<?php

namespace App\Http\APIv1\Resources;

use App\Models\Dictionaries\TicketGrade;
use App\Models\Sails\Trip;
use App\Models\Tickets\TicketRate;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Trip */
class ApiTripResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'start_at' => $this->start_at->translatedFormat('d M Y H:i:s'),
            'end_at' => $this->end_at->translatedFormat('d M Y H:i:s'),
            'start_pier_id' => $this->start_pier_id,
            'end_pier_id' => $this->end_pier_id,
            'ship_id' => $this->ship_id,
            'excursion' => new ApiExcursionResource($this->excursion),
            'tickets_total' => $this->tickets_total,
            'tickets_left' => $this->tickets_total - $this->tickets_count,
            'ticket_grades' => ApiTicketRateResource::collection($this->getRate()
                ->rates->filter(fn (TicketRate $rate) => $rate->base_price > 0)),
        ];
    }
}
