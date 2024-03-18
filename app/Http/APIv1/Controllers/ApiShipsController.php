<?php

namespace App\Http\APIv1\Controllers;

use App\Http\APIv1\Requests\ApiGetShipsRequest;
use App\Http\APIv1\Resources\ApiShipsResource;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\ShipStatus;
use App\Models\Ships\Ship;
use Illuminate\Http\JsonResponse;

class ApiShipsController extends Controller
{
    public function __invoke(ApiGetShipsRequest $request): JsonResponse
    {
        $ships = Ship::where(['status_id' => ShipStatus::active])->get();

        return response()->json(ApiShipsResource::collection($ships));
    }
}
