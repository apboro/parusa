<?php

namespace App\Http\Resources;

use App\Models\TripStop;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TripStop */
class StopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
       return [
           'pier' => $this->pier,
           'start_at' => $this->start_at?->format('H:i'),
           'full_start_at' => $this->start_at?->translatedFormat('j F Y') . ' г.',
           'terminal_price' => $this->terminal_price_delta,
           'partner_price' => $this->partner_price_delta,
           'duration' => $this->start_at->diffInMinutes($this->trip->end_at),
           'site_price' => $this->site_price_delta,
           'not_formatted_start_at' => $this->start_at->toIso8601String()
       ];
    }
}
