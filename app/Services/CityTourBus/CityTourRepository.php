<?php

namespace App\Services\CityTourBus;

use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;


class CityTourRepository
{

    private CityTourApiClientProvider $apiClient;
    public function __construct()
    {
        $this->apiClient = new CityTourApiClientProvider();
    }

    public function getExcursions(array $query = []): array
    {
        return $this->apiClient->get('excursions', $query);
    }
    public function getExcursion(int $path, array $query = []): array
    {
        return $this->apiClient->get('excursions/'.$path, $query);
    }

    public function getSchedule(int $path, array $query = []): array
    {
        return $this->apiClient->get('excursions-schedule/'. $path, $query);
    }

    public function makeOrder(Order $order): ?array
    {
        $query = $this->makeCityTourOrderFromParusaOrder($order);
        if ($query) {
            return $this->apiClient->post('orders', $query);
        } else {
            return null;
        }
    }

    public function approveOrder(Order $order): array
    {
        $orderId = $order->additionalData->provider_order_id;
        $query = ['payment_status' => 1];

        return $this->apiClient->put('orders/'.$orderId, $query);
    }

    public function cancelOrder(Order $order)
    {
        $orderId = $order->additionalData->provider_order_id;
        $query = ['cancellation_request' => 1];

        return $this->apiClient->put('orders/'.$orderId, $query);
    }

    public function deleteOrder(Order $order)
    {
        $orderId = $order->additionalData->provider_order_id;

        return $this->apiClient->delete('orders/'.$orderId);
    }

    public function sendTickets(Order $order)
    {
        $orderId = $order->additionalData->provider_order_id;
        $query = ['send_tickets' => "true"];

        return $this->apiClient->get('orders/'.$orderId, $query);

    }

    public function getOrderInfo(Order $order): array
    {
        $orderId = $order->additionalData->provider_order_id;

        return $this->apiClient->get('orders/' . $orderId);
    }

    public function makeCityTourOrderFromParusaOrder(Order $order): ?array
    {
        $tickets = $order->tickets()
            ->whereIn('status_id', TicketStatus::ticket_countable_statuses)
            ->where('provider_id', Provider::city_tour)
            ->get();
        $params = [];
        $ticketsList = [];
        if ($tickets->isNotEmpty()) {
            foreach ($tickets as $ticket) {
                $ticketsList[$ticket->grade->id] = isset($ticketsList[$ticket->grade->id]) ? $ticketsList[$ticket->grade->id]  + 1 : 1;
            }
            $params = [
                'customer_name'=>$order->name,
                'customer_phone' => $order->phone,
                'customer_email' => $order->email,
                'excursion_id' => $order->tickets[0]->trip->excursion->additionalData->provider_excursion_id,
                'excursion_datetime' => $order->tickets[0]->trip->start_at->format('Y-m-d H:i:s'),
                'payment_status' => 0,
                'tickets' => json_encode($ticketsList),
            ];
        }

        return $params;
    }

}

