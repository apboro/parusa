<?php

namespace App\Events;

use App\Models\Order\Order;
use Illuminate\Foundation\Events\Dispatchable;

//cancel neva travel order
class NevaTravelCancelOrderEvent
{
    use Dispatchable;

    public function __construct(public Order $order)
    {
    }
}
