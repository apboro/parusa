<?php

namespace App\Http\Controllers\API\Ships;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\ShipStatus;
use App\Models\Hit\Hit;
use App\Models\Ships\Ship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipViewController extends ApiController
{
    public function view(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $id = $request->input('id');

        $ship = Ship::query()->where('id', $id)->first();

        if ($id === null || !$ship) {
            return APIResponse::notFound('Теплоход не найден');
        }

        // fill data
        $values = [
            'active' => $ship->hasStatus(ShipStatus::active),
            'id' => $ship->id,
            'name' => $ship->name,
            'description' => $ship->description,
            'capacity' => $ship->capacity,
            'owner' => $ship->owner,
            'status' => $ship->status->name,
            'status_id' => $ship->status_id,
        ];

        // send response
        return APIResponse::response($values);
    }
}
