<?php

namespace App\Services\YagaAPI;

use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\ShipStatus;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Excursions\Excursion;
use App\Models\Sails\Trip;
use App\Models\Ships\Ship;
use App\Services\YagaAPI\Model\City;
use App\Services\YagaAPI\Model\Event;
use App\Services\YagaAPI\Model\Hall;
use App\Services\YagaAPI\Model\Manifest;
use App\Services\YagaAPI\Model\Organizer;
use App\Services\YagaAPI\Model\Session;
use App\Services\YagaAPI\Model\Venue;
use App\Services\YagaAPI\Requests\Schedule\GetEventsRequest;
use App\Services\YagaAPI\Requests\Schedule\GetHallPlanRequest;
use App\Services\YagaAPI\Requests\Schedule\GetHallsRequest;
use App\Services\YagaAPI\Requests\Schedule\GetOrganizersRequest;
use App\Services\YagaAPI\Requests\Schedule\GetScheduleRequest;
use App\Services\YagaAPI\Requests\Schedule\GetVenuesRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class YagaScheduleApiController
{
    #[OA\Get(path: '/api/yaga/manifest', tags: ['Расписание'])]
    #[OA\Response(response: '200', description: '')]
    public function getManifest(): JsonResponse
    {

        return (new Manifest())->getResource();
    }

    #[OA\Get(path: '/api/yaga/cities', tags: ['Расписание'])]
    #[OA\Response(response: '200', description: '')]
    public function getCities(): JsonResponse
    {
        return City::getResource();
    }

    public function getEvents(GetEventsRequest $request): JsonResponse
    {
        $limit = $request->input('limit') ?? 500;
        $dateFrom = $request->input('dateFrom') ? Carbon::createFromTimestamp($request->input('dateFrom')) : now();
        $dateTo = $request->input('dateTo') ? Carbon::createFromTimestamp($request->input('dateTo')) : $dateFrom;
        if ($request->input('eventId') && is_string($request->input('eventId'))){
            $eventsArr = [$request->input('eventId')];
        } else {
            $eventsArr = $request->input('eventId');
        }

        $excursionQuery = Excursion::query()->activeScarletSails($dateFrom, $dateTo)
            ->when($request->input('dateFrom'), function ($excursions) use ($request) {
                $excursions->whereHas('trips', function ($trips) use ($request) {
                    $trips->whereDate('start_at', '<=', Carbon::createFromTimestamp($request->input('dateFrom')));
                });
            })
            ->when($request->input('dateTo'), function ($excursions) use ($request) {
                $excursions->whereHas('trips', function ($trips) use ($request) {
                    $trips->whereDate('end_at', '>=', Carbon::createFromTimestamp($request->input('dateTo')));
                });
            })
            ->when(!empty($request->input('eventId')), function ($excursions) use ($eventsArr) {
                $excursions->whereIn('id', $eventsArr);
            });

        $countEvents = $excursionQuery->clone()->count();

        $excursions = $excursionQuery
            ->take($limit)
            ->skip($request->input('offset'))
            ->get();

        $events = [];
        foreach ($excursions as $excursion) {
            $events[] = (new Event($excursion))->getResource();
        }

        return response()->json([
            'events' => $events,
            'paging' => [
                "limit" => $limit,
                "offset" => $request->input('offset'),
                "total" => $countEvents,
            ]
        ]);
    }

    public function getHallplan(GetHallPlanRequest $request): JsonResponse
    {
        $trip = Trip::where('id', $request->input('sessionId'))
            ->where('status_id', true)
            ->where('sale_status_id', true)
            ->first();

        if (!$trip) {
            return response()->json(['hall' => []]);
        }

        $ship = $trip->ship;
        $rates = $trip->excursion->rateForDate(now())->rates;
        $grades = $rates->map(fn($rate) => $rate->grade);
        $hall = (new Hall($grades, $ship))->getResource();

        return response()->json(['hall' => $hall]);
    }

    public function getHalls(GetHallsRequest $request): JsonResponse
    {
        $shipsInActiveTrips = Trip::activeScarletSails()->distinct()->pluck('ship_id')->toArray();

        $ship = Ship::where('id', $request->input('venueId'))->first();
        $grades = TicketGrade::where('provider_id', Provider::scarlet_sails)->get();
        $halls = [];

        if ($request->input('hallId') && is_string($request->input('hallId'))){
            $hallsArr = [$request->input('hallId')];
        } else {
            $hallsArr = $request->input('hallId');
        }

        if (!$ship) {
            if (!empty($hallsArr)) {
                $filteredQueryArr = array_intersect($shipsInActiveTrips, $hallsArr);
            }
            $ships = Ship::whereIn('id', $filteredQueryArr ?? $shipsInActiveTrips)->get();
            foreach ($ships as $ship) {
                $halls[] = (new Hall($grades, $ship))->getResource($request);
            }
        } else {
            $halls[] = (new Hall($grades, $ship))->getResource($request);
        }

        return response()->json([
            'halls' => $halls,
            'paging' => [
                "limit" => $request->input('limit'),
                "offset" => $request->input('offset'),
                "total" => count($halls),
            ]
        ]);

    }

    public function getOrganizers(GetOrganizersRequest $request): JsonResponse
    {
        if ($request->input('organizerId') && is_string($request->input('organizerId'))){
            $organizersArr = [$request->input('organizerId')];
        } else {
            $organizersArr = $request->input('organizerId');
        }

        $organizer = Organizer::getStaticResource();
        if ($request->input('offset') > 0 || (!empty($request->input('organizerId')) && !in_array(1, $organizersArr))) {
            $organizer = [];
        }

        return response()->json([
            'organizers' => $organizer,
            'paging' => [
                "limit" => $request->input('limit'),
                "offset" => $request->input('offset'),
                "total" => empty($organizer) ? 0 : 1,
            ]
        ]);
    }

    public function getSchedule(GetScheduleRequest $request): JsonResponse
    {
        $limit = $request->input('limit') ?? 500;
        if ($request->input('sessionId') && is_string($request->input('sessionId'))){
            $sessionsArr = [$request->input('sessionId')];
        } else {
            $sessionsArr = $request->input('sessionId');
        }

        $tripsQuery = Trip::query()->activeScarletSails()
            ->when(!empty($request->input('sessionId')), fn($trips) => $trips->whereIn('id', $sessionsArr))
            ->when($request->input('venueId'), fn($trips) => $trips->where('ship_id', $request->input('venueId')));

        $sessionsCount = $tripsQuery->clone()->count();

        $trips = $tripsQuery->skip($request->input('offset'))
            ->take($limit)
            ->get();

        $sessions = [];
        foreach ($trips as $trip) {
            $sessions[] = (new Session())->getResource($trip);
        }

        return response()->json([
            'sessions' => $sessions,
            'paging' => [
                "limit" => $limit,
                "offset" => $request->input('offset'),
                "total" => $sessionsCount,
            ]
        ]);
    }

    public function getVenues(GetVenuesRequest $request): JsonResponse
    {
        if ($request->input('venueId') && is_string($request->input('venueId'))) {
            $venuesArr = [$request->input('venueId')];
        } else {
            $venuesArr = $request->input('venueId');
        }
        if ($request->input('cityId') && $request->input('cityId') != 1) {
            return response()->json([
                'venues' => [],
                'paging' => [
                    "limit" => $request->input('limit'),
                    "offset" => $request->input('offset'),
                    "total" => 0,
                ]
            ]);
        }

        $shipsInActiveTrips = Trip::activeScarletSails()->distinct()->pluck('ship_id')->toArray();
        $shipsQuery = Ship::query()
            ->whereIn('id', $shipsInActiveTrips)
            ->where('provider_id', Provider::scarlet_sails)
            ->where('status_id', ShipStatus::active)
            ->when(!empty($venuesArr), function ($ship) use ($venuesArr) {
                $ship->whereIn('id', $venuesArr);
            })
            ->when($request->input('updatedAfter'), function ($ship) use ($request) {
                $ship->whereDate('updated_at', '>', Carbon::createFromTimestamp($request->input('updatedAfter')));
            });

        $countShips = $shipsQuery->clone()->count();

        $ships = $shipsQuery->skip($request->input('offset'))->take($request->input('limit') ?? 500)->get();

        $venues = [];
        foreach ($ships as $ship) {
            $venues[] = (new Venue($ship))->getResource();
        }

        return response()->json([
            'venues' => $venues,
            'paging' => [
                "limit" => $request->input('limit'),
                "offset" => $request->input('offset'),
                "total" => $countShips,
            ]
        ]);

    }

}
