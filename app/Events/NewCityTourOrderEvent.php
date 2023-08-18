<?php

namespace App\Events;

use App\Models\Order\Order;
use Illuminate\Foundation\Events\Dispatchable;

class NewCityTourOrderEvent
{
    use Dispatchable;

    public function __construct(public Order $order)
    {
    }
}
