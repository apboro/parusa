<?php

namespace App\Http\APIv1\Controllers;

use App\Http\APIv1\Requests\ApiGetExcursionsRequest;
use App\Http\APIv1\Resources\ApiExcursionResource;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Dictionaries\Provider;
use App\Models\Excursions\Excursion;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiExcursionsController extends Controller
{
    public function __invoke(ApiGetExcursionsRequest $request): JsonResponse
    {
        $excursions = Excursion::where([
            'status_id' => ExcursionStatus::active,
            'use_seat_scheme' => false,
        ])->get();

        return response()->json(ApiExcursionResource::collection($excursions));
    }
}
