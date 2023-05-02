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

    public function getPiersInfo(array $query = []): array
    {
        return $this->apiClient->get('get_piers_info', $query);
    }

    public function getShipsInfo(array $query = []): array
    {
        return $this->apiClient->get('get_ships_info', $query);
    }

    public function getProgramsInfo(array $query = []): array
    {
        return $this->apiClient->get('get_programs_info', $query);
    }

    public function getCruisesInfo(array $query = []): array
    {
        return $this->apiClient->get('get_cruises_info', $query);
    }

    public function getSchedule(array $query = [])
    {
        return $this->apiClient->get('get_schedule', $query);
    }

    public function makeOrder(array $query = []): array
    {
        return $this->apiClient->post('request_ticket_order', $query);
    }

    public function checkCanOrderTickets($trip_external_id)
    {
        $trip = $this->getCruisesInfo(['point_id'=>$trip_external_id]);
        //many (есть, много), less_than_10 (есть, менее 10), less_than_3 (есть, менее 3), none (нет мест)
        return $trip['default_arrival']['prices_table']['available_seats'];
    }
}
