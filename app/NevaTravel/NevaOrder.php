<?php

namespace App\NevaTravel;

use App\Models\Order\Order;

class NevaOrder
{
    private NevaTravelRepository $nevaApiData;

    /**
     * @param NevaTravelRepository $nevaApiData
     */
    public function __construct()
    {
        $this->nevaApiData = new NevaTravelRepository();
    }

    public function make(Order $order): bool
    {
        $result = $this->nevaApiData->makeOrder($order);
        if ($result['status'] != 200) {
            return false;
        }
        $order->neva_travel_id = $result['body']['id'];
        $order->save();
        return true;
    }

    public function approve(Order $order)
    {
        return $this->nevaApiData->approveOrder($order->neva_travel_id);
    }

    public function getOrderInfo(Order $order)
    {
        return $this->nevaApiData->getOrderInfo($order->neva_travel_id);
    }
}
