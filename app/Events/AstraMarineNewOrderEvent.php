<?php

namespace App\Events;

use App\Models\Order\Order;
use App\Services\AstraMarine\AstraMarineOrder;
use Illuminate\Foundation\Events\Dispatchable;

class AstraMarineNewOrderEvent
{
    use Dispatchable;

    public function __construct(public Order $order)
    {
        (new AstraMarineOrder($order))->bookSeats();
        (new AstraMarineOrder($order))->registerOrder();
    }
}
