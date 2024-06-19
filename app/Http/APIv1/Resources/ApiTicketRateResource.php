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
            'grade_id' => $this->grade->id,
            'grade_name' => $this->grade->name,
            'price' => $this->partner_price ?? $this->base_price,
        ];
    }
}
