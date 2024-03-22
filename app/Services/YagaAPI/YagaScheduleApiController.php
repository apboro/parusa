<?php

namespace App\Services\YagaAPI;

use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\ShipStatus;
use App\Models\Ships\Ship;
use App\Services\YagaAPI\Model\City;
use App\Services\YagaAPI\Model\Manifest;
use App\Services\YagaAPI\Model\Venue;
use App\Services\YagaAPI\Requests\GetVenuesRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class YagaScheduleApiController implements YagaScheduleApiInterface
{
    #[OA\Get(path: '/api/yaga/manifest', tags: ['Расписание'])]
    #[OA\Response(response: '200', description: '')]
    public function getManifest(): JsonResponse
    {

        return (new Manifest())->getResource();
    }

    #[OA\Get(path: '/api/yaga/cities', tags: ['Расписание'])]
    #[OA\Response(response: '200', description: '')]
    public function getCities($offset = null, $limit = null, array $cityId = null, Carbon $updatedAfter = null)
    {

        return City::getResource();
    }

    #[OA\Get(path: '/api/yaga/events', tags: ['Расписание'])]
    #[OA\Response(response: '200', description: '')]
    public function getEvents($offset = null, $limit = null, array $eventId = null, Carbon $dateFrom = null, Carbon $dateTo = null, Carbon $updatedAfter = null)
    {

    }

    #[OA\Get(path: '/api/yaga/hallplan', tags: ['Расписание'])]
    #[OA\Response(response: '200', description: '')]
    public function getHallplan($sessionId = null)
    {

    }

    #[OA\Get(path: '/api/yaga/halls', tags: ['Расписание'])]
    #[OA\Response(response: '200', description: '')]
    public function getHalls($offset = null, $limit = null, $venueId = null, array $hallId = null, Carbon $updatedAfter = null)
    {

    }

    #[OA\Get(path: '/api/yaga/organizers', tags: ['Расписание'])]
    #[OA\Response(response: '200', description: '')]
    public function getOrganizers($offset = null, $limit = null, array $organizerId = null, Carbon $updatedAfter = null)
    {

    }

    #[OA\Get(path: '/api/yaga/persons', tags: ['Расписание'])]
    #[OA\Response(response: '200', description: '')]
    public function getPersons($offset = null, $limit = null, array $personId = null, Carbon $updatedAfter = null)
    {

    }

    #[OA\Get(path: '/api/yaga/schedule', tags: ['Расписание'])]
    #[OA\Response(response: '200', description: '')]
    public function getSchedule($offset = null, $limit = null, $venueId = null, array $sessionId = null, Carbon $updatedAfter = null)
    {

    }

    public function getVenues(GetVenuesRequest $request)
    {
        if ($request->get('cityId') && $request->get('cityId') != 1){
            return response()->json();
        }
        $shipsQuery = Ship::query()
            ->where('provider_id', Provider::scarlet_sails)
            ->where('status_id', ShipStatus::active)
            ->when($request->get('venueId'), function ($ship) use ($request) {
                $ship->where('id', $request->get('venueId'));
            })
            ->when($request->get('updatedAfter'), function ($ship) use ($request) {
                $ship->whereDate('updated_at', '>', Carbon::createFromTimestamp($request->get('updatedAfter')));
            });

        $countShipsQuery = $shipsQuery->clone();
        $countShips = $countShipsQuery->get()->count();

        $ships = $shipsQuery->when($request->get('limit'), function ($query) use ($request) {
            $query->skip($request->get('offset'));
            $query->take($request->get('limit'));
        })
            ->get();

        $venues = [];
        foreach ($ships as $ship) {
            $venues[] = (new Venue($ship))->getResource();
        }

        return response()->json([
            'venues' => $venues,
            'paging' => [
                "limit" => $request->get('limit'),
                "offset" => $request->get('offset'),
                "total" => $countShips,
            ]
        ]);

    }

}
