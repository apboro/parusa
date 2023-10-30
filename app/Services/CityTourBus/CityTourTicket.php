<?php

namespace App\Services\CityTourBus;

use App\Models\Order\Order;

class CityTourTicket
{
    private Order $order;
    private CityTourRepository $cityTourRepository;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->cityTourRepository = new CityTourRepository();
    }

    public function getTickets()
    {

    }

    public function sendTicketsOfCityTour(Order $order)
    {
        $orderId = $order->additionalData->provider_order_id;
        $query = ['send_tickets' => "true"];

        return $this->apiClient->get('orders/'.$orderId, $query);
    }
}
