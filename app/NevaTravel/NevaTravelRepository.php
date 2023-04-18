<?php

namespace App\NevaTravel;

use Facade\FlareClient\Api;

class NevaTravelRepository
{
    private ApiClientProvider $apiClient;

    public function __construct()
    {
        $this->apiClient = new ApiClientProvider();
    }

    public function getPiersInfo(array $query = [])
    {
        return $this->apiClient->get('get_piers_info', $query);
    }

    public function getShipsInfo(array $query = [])
    {
        return $this->apiClient->get('get_ships_info', $query);
    }

    public function getProgramsInfo(array $query = [])
    {
        return $this->apiClient->get('get_programs_info', $query);
    }

    public function getCruisesInfo(array $query = [])
    {
        return $this->apiClient->get('get_cruises_info', $query);
    }

    public function getSchedule(array $query = [])
    {
        return $this->apiClient->get('get_schedule', $query);
    }
}
