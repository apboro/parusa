<?php

namespace App\Services\YagaAPI;

use App\Services\YagaAPI\Model\City;
use App\Services\YagaAPI\Model\Manifest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
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
    public function getCities($offset = null, $limit = null, array $cityId = null, Carbon $updatedAfter = null){

        return City::getResource();
    }

    #[OA\Get(path: '/api/yaga/events', tags: ['Расписание'])]
    #[OA\Response(response: '200', description: '')]
    public function getEvents($offset = null, $limit = null, array $eventId = null, Carbon $dateFrom = null, Carbon $dateTo = null, Carbon $updatedAfter = null){

    }
    #[OA\Get(path: '/api/yaga/hallplan', tags: ['Расписание'])]
    #[OA\Response(response: '200', description: '')]
    public function getHallplan($sessionId = null){

    }
    #[OA\Get(path: '/api/yaga/halls', tags: ['Расписание'])]
    #[OA\Response(response: '200', description: '')]
    public function getHalls($offset = null, $limit = null, $venueId = null, array $hallId = null, Carbon $updatedAfter = null){

    }
    #[OA\Get(path: '/api/yaga/organizers', tags: ['Расписание'])]
    #[OA\Response(response: '200', description: '')]
    public function getOrganizers($offset = null, $limit = null, array $organizerId = null, Carbon $updatedAfter = null){

    }
    #[OA\Get(path: '/api/yaga/persons', tags: ['Расписание'])]
    #[OA\Response(response: '200', description: '')]
    public function getPersons($offset = null, $limit = null, array $personId = null, Carbon $updatedAfter = null){

    }
    #[OA\Get(path: '/api/yaga/schedule', tags: ['Расписание'])]
    #[OA\Response(response: '200', description: '')]
    public function getSchedule($offset = null, $limit = null, $venueId = null, array $sessionId = null, Carbon $updatedAfter = null){

    }
    #[OA\Get(path: '/api/yaga/venues', tags: ['Расписание'])]
    #[OA\Response(response: '200', description: '')]
    public function getVenues($offset = null, $limit = null, $cityId = null, array $venueId = null, Carbon $updatedAfter = null){

    }

}
