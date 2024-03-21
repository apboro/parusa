<?php

namespace App\Services\YagaAPI;

use Carbon\Carbon;

interface YagaScheduleApiInterface
{
    public function getCities($offset = null, $limit = null, array $cityId = null, Carbon $updatedAfter = null);
    public function getEvents($offset = null, $limit = null, array $eventId = null, Carbon $dateFrom = null, Carbon $dateTo = null, Carbon $updatedAfter = null);
    public function getHallplan($sessionId = null);
    public function getHalls($offset = null, $limit = null, $venueId = null, array $hallId = null, Carbon $updatedAfter = null);
    public function getManifest();
    public function getOrganizers($offset = null, $limit = null, array $organizerId = null, Carbon $updatedAfter = null);
    public function getPersons($offset = null, $limit = null, array $personId = null, Carbon $updatedAfter = null);
    public function getSchedule($offset = null, $limit = null, $venueId = null, array $sessionId = null, Carbon $updatedAfter = null);
    public function getVenues($offset = null, $limit = null, $cityId = null, array $venueId = null, Carbon $updatedAfter = null);
}
