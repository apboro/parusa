<?php

namespace App\Http\Controllers\API\Trips;

use App\Http\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\SeatStatus;
use App\Models\Sails\Trip;
use App\Models\Ships\Seats\TripSeat;
use App\Services\AstraMarine\AstraMarineRepository;
use Illuminate\Http\Request;

class TripSeatsController extends Controller
{
    public function __invoke(Request $request)
    {
        $trip = Trip::where('id', $request->tripId)->with(['ship', 'ship.seats'])->first();
        TripSeat::where('trip_id', $trip->id)
            ->whereNull('status_id')
            ->update(['status_id' => SeatStatus::vacant]);

        if ($trip->provider_id === Provider::astra_marine) {
            $seats = (new AstraMarineRepository())->getSeatsOnEvent(['eventID' => $trip->additionalData?->provider_trip_id]);
            foreach ($seats['body']['seats'] as $seat) {
                if ($seat['seatStatus'] !== "Свободно") {
                    TripSeat::updateOrCreate([
                        'trip_id' => $trip->id,
                        'seat_id' => $trip->ship->seats()
                            ->where('provider_seat_id', $seat['seatID'])
                            ->first()
                            ->id],
                        ['status_id' => SeatStatus::occupied]
                    );
                }
            }
        }

        return APIResponse::response(['seats' => $trip->getSeats()]);
    }
}
