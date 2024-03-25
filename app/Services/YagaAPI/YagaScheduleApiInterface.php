<?php

namespace App\Services\YagaAPI;

use App\Services\YagaAPI\Requests\GetHallsRequest;
use App\Services\YagaAPI\Requests\GetVenuesRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

interface YagaScheduleApiInterface
{
    public function getCities($offset = null, $limit = null, array $cityId = null, Carbon $updatedAfter = null);
    public function getEvents($offset = null, $limit = null, array $eventId = null, Carbon $dateFrom = null, Carbon $dateTo = null, Carbon $updatedAfter = null);
    public function getHallplan($sessionId = null);
    public function getHalls(GetHallsRequest $request);
    public function getManifest();
    public function getOrganizers($offset = null, $limit = null, array $organizerId = null, Carbon $updatedAfter = null);
    public function getPersons($offset = null, $limit = null, array $personId = null, Carbon $updatedAfter = null);
    public function getSchedule($offset = null, $limit = null, $venueId = null, array $sessionId = null, Carbon $updatedAfter = null);
    public function getVenues(GetVenuesRequest $request);
}
