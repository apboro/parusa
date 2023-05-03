<?php

namespace App\NevaTravel;

use App\Models\Order\Order;
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

    public function makeOrder(Order $order): array
    {
        $query = $this->makeNevaOrderFromParusaOrder($order);
        return $this->apiClient->post('request_ticket_order', $query);
    }

    public function checkCanOrderTickets($trip_external_id)
    {
        $trip = $this->getCruisesInfo(['point_id' => $trip_external_id]);

        //many (есть, много), less_than_10 (есть, менее 10), less_than_3 (есть, менее 3), none (нет мест)
        return $trip['body'][0]['default_arrival']['prices_table'][0]['available_seats'];
    }

    public function makeNevaOrderFromParusaOrder(Order $order)
    {
        foreach ($order->tickets as $ticket) {
            $ticket_list[] =
                [
                    'departure_point_id' => $ticket->trip->external_id,
                    'program_price_id' => $ticket->trip->program_price_id,
                    'ticket_category' => $ticket->grade->external_grade_name,
                    'purchase_price' => $ticket->base_price,
                    'qty' => 1
                ];

            $params = [
                'ticket_list' => $ticket_list,
                'hide_ticket_price' => true,
                'client_name' => $order->name,
                'client_phone' => $order->phone,
                'client_email' => $order->email,
                'comment' => ''
            ];
        }

        return $params;

    }
}
