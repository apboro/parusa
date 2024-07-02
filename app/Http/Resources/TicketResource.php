<?php

namespace App\Http\Resources;

use App\Http\APIv1\Resources\ApiOrderResource;
use App\Http\APIv1\Resources\ApiTripResource;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Tickets\Ticket */
class TicketResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'base_price' => $this->base_price,
            'seat_id' => $this->seat_id,
            'trip_id' => $this->trip_id,
            'grade_id' => $this->grade_id,
            'qr-code' => $this->additionalData?->provider_qr_code ?? $this->additionalData?->provider_order_id ?? $this->qrData(),
        ];
    }
}
