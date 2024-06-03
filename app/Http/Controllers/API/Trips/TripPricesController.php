<?php

namespace App\Http\Controllers\API\Trips;

use App\Actions\GetNevaTripPriceAction;
use App\Http\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\SeatStatus;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Sails\Trip;
use App\Models\Ships\Seats\TripSeat;
use App\Services\AstraMarine\AstraMarineRepository;
use App\Services\NevaTravel\NevaTravelRepository;
use Illuminate\Http\Request;

class TripPricesController extends Controller
{
    public function __invoke(Request $request)
    {
        $trip = Trip::find($request->trip['id']);
        $prices = (new GetNevaTripPriceAction())->run($trip);

        return APIResponse::response($prices);
    }
}
