<?php

namespace App\Http\APIv1\Resources;

use App\Models\Tickets\TicketRate;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TicketRate */
class ApiTicketRateResource extends JsonResource
{
    public function toArray($request): array
    {
        $this->loadMissing('grade');
        return [
            'type_id' => $this->grade->id,
            'type_name' => $this->grade->name,
            'price' => $this->base_price,
        ];
    }
}
