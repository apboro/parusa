<?php

namespace App\Services\YagaAPI;

use App\Models\Sails\Trip;
use App\Services\YagaAPI\Model\AvailableSeats;
use App\Services\YagaAPI\Requests\Order\AvailableSeatsRequest;
use App\Services\YagaAPI\Requests\Order\ReserveTicketsRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class YagaOrderApiController
{
    public function availableSeats(AvailableSeatsRequest $request): JsonResponse
    {
        $trip = Trip::with(['excursion', 'excursion.ratesLists', 'tickets'])->where('id', ($request->input('sessionId')))->first();
        $results = AvailableSeats::getResource($trip);

        return response()->json([$results]);
    }


    public function reserve(ReserveTicketsRequest $request)
    {

    }

    public function orderInfo()
    {
    }

    public function orderStatus()
    {
    }

    public function cancelOrder()
    {
    }

    public function checkPromocode()
    {
    }

    public function clearReservation()
    {
    }

    public function approve()
    {
    }

}
