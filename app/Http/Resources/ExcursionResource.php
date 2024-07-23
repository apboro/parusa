<?php

namespace App\Http\Resources;

use App\Http\APIv1\Resources\ApiExcursionResource;
use App\Http\APIv1\Resources\ApiTripResource;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Excursions\Excursion */
class ExcursionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_single_ticket' => $this->is_single_ticket,
            'reverse_excursion_id' => $this->reverse_excursion_id,
            'type_id' => $this->type_id,
            'excursion_first_image_url' => $this->images->isNotEmpty() ? $this->images[0]?->url : null,
            'use_seat_scheme' => $this->use_seat_scheme,
            'disk_url' => $this->disk_url,
            'provider_id' => $this->provider_id,
        ];
    }
}
