<?php

namespace App\Http\APIv1\Resources;

use App\Models\Excursions\Excursion;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Excursion */
class ApiExcursionResource extends JsonResource
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
