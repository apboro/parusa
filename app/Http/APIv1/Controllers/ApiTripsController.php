<?php

namespace App\Http\APIv1\Controllers;

use App\Http\APIResponse;
use App\Http\APIv1\Requests\ApiGetTripsRequest;
use App\Http\APIv1\Resources\ApiTripResource;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Sails\Trip;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class ApiTripsController extends Controller
{
    public function __invoke(ApiGetTripsRequest $request): JsonResponse
    {
        $trips = Trip::query()
            ->with(['excursion',
                'excursion.images',
                'excursion.info',
                'excursion.ratesLists',
                'excursion.ratesLists.rates',
                'excursion.ratesLists.rates.grade'])
            ->withCount(['tickets' => function (Builder $query) {
                $query->whereIn('status_id', TicketStatus::ticket_countable_statuses);
            }])
            //filter by date
            ->when($request->date, fn($query) => $query->whereDate('start_at', Carbon::parse($request->date)))
            ->when(!$request->date, fn($query) => $query->whereDate('start_at', today()))
            //filter by excursion
            ->when(!empty($request->excursion_ids), fn($query) => $query
                ->whereHas('excursion', fn($excursion) => $excursion->whereIn('id', $request->excursion_ids)))
            ->where('start_at', '>=', now())
            ->where('status_id', TripStatus::regular)
            ->where('sale_status_id', TripSaleStatus::selling)
            ->whereHas('excursion', fn($excursion) => $excursion
                ->where('status_id', ExcursionStatus::active)
                ->where('only_site', 0)
                ->where('use_seat_scheme', false))
            ->whereHas('excursion.ratesLists', function (Builder $query) {
                $query->whereDate('start_at', '<=', now())->whereDate('end_at', '>=', now());
            })
            ->get();

        return response()->json(ApiTripResource::collection($trips));
    }
}
