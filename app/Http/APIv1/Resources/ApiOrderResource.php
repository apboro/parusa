<?php

namespace App\Http\APIv1\Resources;

use App\Http\Resources\TicketResource;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Order\Order */
class ApiOrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => $this->status->name,
            'client_name' => $this->name,
            'client_email' => $this->email,
            'client_phone' => $this->phone,
            'created_at' => $this->created_at->format('Y-m-d'),
            'tickets' => TicketResource::collection($this->tickets),
        ];
    }
}
