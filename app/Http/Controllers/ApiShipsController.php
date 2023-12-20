<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiGetShipsRequest;
use App\Http\Resources\ApiShipsResource;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\ShipStatus;
use App\Models\Ships\Ship;

class ApiShipsController extends Controller
{
    public function __invoke(ApiGetShipsRequest $request)
    {
        $ships = Ship::where(['provider_id' => Provider::scarlet_sails, 'status_id' => ShipStatus::active])->get();

        return response()->json(ApiShipsResource::collection($ships));
    }
}
