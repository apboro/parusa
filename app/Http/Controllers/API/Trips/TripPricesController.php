<?php

namespace App\Http\Controllers\API\Trips;

use App\Http\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Sails\Trip;
use App\Services\NevaTravel\GetNevaTripPriceAction;
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
