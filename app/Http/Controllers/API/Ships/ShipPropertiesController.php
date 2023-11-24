<?php

namespace App\Http\Controllers\API\Ships;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Hit\Hit;
use App\Models\Ships\Ship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipPropertiesController extends ApiController
{
    /**
     * Update pier status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function properties(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $id = $request->input('id');

        $ship = Ship::query()->where('id', $id)->first();

        if ($id === null || !$ship) {
            return APIResponse::notFound('Теплоход не найден');
        }

        $value = $request->input('data.value');

        $ship->setStatus($value);

        return APIResponse::response([], [
            'status' => $ship->status->name,
            'status_id' => $ship->status_id,
            'active' => $ship->hasStatus(PositionStatus::active),
        ], "Данные теплохода обновлены");
    }
}
