<?php

namespace App\Http\APIv1\Controllers;

use App\Http\APIv1\Requests\ApiGetPiersRequest;
use App\Http\APIv1\Resources\ApiPiersResource;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\PiersStatus;
use App\Models\Dictionaries\Provider;
use App\Models\Piers\Pier;
use Illuminate\Http\JsonResponse;

class ApiPiersController extends Controller
{
    public function __invoke(ApiGetPiersRequest $request): JsonResponse
    {
        $piers = Pier::where(['provider_id'=>Provider::scarlet_sails, 'status_id' => PiersStatus::active])->get();

        return response()->json(ApiPiersResource::collection($piers));

    }
}
