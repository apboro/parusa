<?php

namespace App\Services\YagaAPI15;

use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\ShipStatus;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Excursions\Excursion;
use App\Models\Sails\Trip;
use App\Models\Ships\Ship;
use App\Services\YagaAPI15\Model\City;
use App\Services\YagaAPI15\Model\Event;
use App\Services\YagaAPI15\Model\Hall;
use App\Services\YagaAPI15\Model\Manifest;
use App\Services\YagaAPI15\Model\Organizer;
use App\Services\YagaAPI15\Model\Session;
use App\Services\YagaAPI15\Model\Venue;
use App\Services\YagaAPI15\Requests\Schedule\GetEventsRequest;
use App\Services\YagaAPI15\Requests\Schedule\GetHallPlanRequest;
use App\Services\YagaAPI15\Requests\Schedule\GetHallsRequest;
use App\Services\YagaAPI15\Requests\Schedule\GetOrganizersRequest;
use App\Services\YagaAPI15\Requests\Schedule\GetScheduleRequest;
use App\Services\YagaAPI15\Requests\Schedule\GetVenuesRequest;
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
        if ($request->input('eventId') && is_string($request->input('eventId'))) {
            $eventsArr = [$request->input('eventId')];
        } else {
            $eventsArr = $request->input('eventId');
        }

        $excursionQuery = Excursion::query()->activeScarletSails($dateFrom, $dateTo, 15)
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

        $rates = $trip->excursion->rateForDate(now())->rates;
        $grades = $rates->map(fn($rate) => $rate->grade);
        $hall = (new Hall($grades, $trip))->getResource();

        return response()->json(['hall' => $hall]);
    }

    public function getHalls(GetHallsRequest $request): JsonResponse
    {
        if ($request->input('hallId') && is_string($request->input('hallId'))) {
            $hallsArr = [$request->input('hallId')];
        } else {
            $hallsArr = $request->input('hallId');
        }

        $activeTrips = Trip::activeScarletSails(15)->groupBy('start_pier_id')
            ->when(!empty($hallsArr), function ($trip) use ($hallsArr) {
                $trip->whereIn('start_pier_id', $hallsArr);
            })
            ->when($request->input('updatedAfter'), function ($trip) use ($request) {
                $trip->whereDate('updated_at', '>', Carbon::createFromTimestamp($request->input('updatedAfter')));
            });

        $trips = $activeTrips->skip($request->input('offset'))->take($request->input('limit') ?? 500)->get();

        $grades = TicketGrade::whereIn('provider_id', [Provider::scarlet_sails, Provider::neva_travel])->get();

        $halls = [];
        foreach ($trips as $trip) {
            $halls[] = (new Hall($grades, $trip))->getResource();
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
        if ($request->input('organizerId') && is_string($request->input('organizerId'))) {
            $organizersArr = [$request->input('organizerId')];
        } else {
            $organizersArr = $request->input('organizerId');
        }

        $organizer = Organizer::getStaticResource();
        if ($request->input('offset') > 0 || (!empty($request->input('organizerId')) && !in_array(1, $organizersArr))) {
            $organizer = [];
        }

        return response()->json([
            'organizers' => [$organizer],
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
        if ($request->input('sessionId') && is_string($request->input('sessionId'))) {
            $sessionsArr = [$request->input('sessionId')];
        } else {
            $sessionsArr = $request->input('sessionId');
        }

        $tripsQuery = Trip::query()->activeScarletSails(15)
            ->when(!empty($sessionsArr), function ($trip) use ($sessionsArr) {
                $trip->whereIn('id', $sessionsArr);
            })
            ->when($request->input('venueId'), function ($trip) use ($request){
                $trip->where('start_pier_id', $request->input('venueId'));
            });

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

        $activeTrips = Trip::activeScarletSails(15)->groupBy('start_pier_id')
            ->when(!empty($venuesArr), function ($trip) use ($venuesArr) {
                $trip->whereIn('start_pier_id', $venuesArr);
            })
            ->when($request->input('updatedAfter'), function ($trip) use ($request) {
                $trip->whereDate('updated_at', '>', Carbon::createFromTimestamp($request->input('updatedAfter')));
            });

        $trips = $activeTrips->skip($request->input('offset'))->take($request->input('limit') ?? 500)->get();

        $venues = [];
        foreach ($trips as $trip) {
            $venues[] = (new Venue($trip))->getResource();
        }

        return response()->json([
            'venues' => $venues,
            'paging' => [
                "limit" => $request->input('limit'),
                "offset" => $request->input('offset'),
                "total" => count($venues),
            ]
        ]);

    }

}
