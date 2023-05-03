<?php

namespace App\NevaTravel;

use App\Models\Order\Order;

class NevaMakeOrder
{
    public function run(Order $order): array
    {
        $nevaApiData = new NevaTravelRepository();
        return $nevaApiData->makeOrder($order);
    }
}
