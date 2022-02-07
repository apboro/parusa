<?php

namespace App\Http\Controllers\API\Sails;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Sails\Trip;
use App\Models\Sails\TripChain;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripInfoController extends ApiController
{
    /**
     * Get info for trip.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function info(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null || null === ($trip = Trip::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Рейс не найден');
        }
        /** @var Trip $trip */
        /** @var TripChain $chain */
        $chain = $trip->chains()->first();

        /** @var Trip $oldest */
        $oldest = $chain->trips()->min('start_at');
        /** @var Trip $latest */
        $latest = $chain->trips()->max('start_at');

        return APIResponse::response([
                'date_from' => Carbon::parse($oldest)->format('d.m.Y'),
                'date_to' => Carbon::parse($latest)->format('d.m.Y'),
                'count' => $chain->trips()->count(),
            ]);
    }
}
