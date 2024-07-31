<?php

namespace App\Http\Resources;

use App\Http\APIv1\Resources\ApiExcursionResource;
use App\Http\APIv1\Resources\ApiTripResource;
use App\Models\Common\Image;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'images' => $this->images->map(function (Image $image) {
                try {
                    return 'data:' . $image->mime . ';base64, ' . base64_encode(Storage::disk($image->disk)->get($image->filename));
                } catch (FileNotFoundException $e){
                    return null;
                }
            }),
            'info' => $this->info,
        ];
    }
}
