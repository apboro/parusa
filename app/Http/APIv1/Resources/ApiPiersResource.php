<?php

namespace App\Http\APIv1\Resources;

use App\Models\Piers\Pier;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Pier */
class ApiPiersResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->info->description,
            'images' => $this->images->map(fn($image) => url($image['filename'])),
        ];
    }
}
