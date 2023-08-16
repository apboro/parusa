<?php

namespace App\Services\CityTourBus;

use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Services\NevaTravel\NevaTravelApiClientProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class CityTourRepository
{

    public function __construct(private readonly CityTourApiClientProvider $apiClient)
    {
    }

    public function getExcursions(array $query = []): array
    {
        return $this->apiClient->get('excursions', $query);
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

    public function makeOrder(Order $order): ?array
    {
        $query = $this->makeNevaOrderFromParusaOrder($order);
        if ($query) {
            return $this->apiClient->post('request_ticket_order', $query);
        } else {
            return null;
        }
    }

    public function approveOrder(string $query = ''): array
    {
        return $this->apiClient->post('approve_order?order_id=' . $query);
    }

    public function getOrderInfo(string $query = ''): array
    {
        return $this->apiClient->post('get_order_info?order_id=' . $query);
    }

    public function commentOrder(array $query = []): array
    {
        return $this->apiClient->post('comment_order', $query);
    }

    public function makeComboTemplateOrder(array $query = []): array
    {
        return $this->apiClient->post('request_combo_template_order', $query);
    }




}

