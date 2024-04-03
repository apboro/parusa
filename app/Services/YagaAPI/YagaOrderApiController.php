<?php

namespace App\Services\YagaAPI;

use App\Models\Sails\Trip;
use App\Services\YagaAPI\Model\AvailableSeats;
use App\Services\YagaAPI\Requests\Order\AvailableSeatsRequest;
use App\Services\YagaAPI\Requests\Order\ReserveTicketsRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class YagaOrderApiController
{
    public function availableSeats(AvailableSeatsRequest $request): JsonResponse
    {
        $data = $request->all();

        if (!$data['sessionId']) {
            return response()->json();
        }

        $trip = Trip::with(['excursion', 'excursion.ratesLists', 'tickets'])->where('id', $data['sessionId'])->first();

        if (!$trip) {
            return response()->json();
        }

        if (!empty($data['venueId']) && $trip->ship->id != $data['venueId']) {
            return response()->json();
        }
        if (!empty($data['eventId']) && $trip->excursion->id != $data['eventId']) {
            return response()->json();
        }
        if (!empty($data['hallId']) && $trip->ship->id != $data['hallId']) {
            return response()->json();
        }
        if (!empty($data['sessionTime']) && $trip->start_at->timestamp != $data['sessionTime']){
            return response()->json();
        }

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
