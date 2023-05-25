<?php

namespace App\NevaTravel;

use App\Models\Order\Order;

class NevaOrder
{
    private Order $order;
    private NevaTravelRepository $nevaApiData;

    /**
     * @param NevaTravelRepository $nevaApiData
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->nevaApiData = new NevaTravelRepository();
    }

    public function make(): bool
    {
        $result = $this->nevaApiData->makeOrder($this->order);
        if ($result['status'] != 200) {
            return false;
        }
        $this->order->neva_travel_id = $result['body']['id'];
        $this->order->save();
        return true;
    }

    public function approve(): array
    {
        return $this->nevaApiData->approveOrder($this->order->neva_travel_id);
    }

    public function cancel(): array
    {
        return $this->nevaApiData->cancelOrder(['id' => $this->order->neva_travel_id,  'comment' => 'Клиент потребовал возврат']);
    }

    public function getOrderInfo()
    {
        return $this->nevaApiData->getOrderInfo($this->order->neva_travel_id);
    }
}
