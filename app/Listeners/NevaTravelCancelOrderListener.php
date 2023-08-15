<?php

namespace App\Listeners;

use App\Events\NevaTravelCancelOrderEvent;
use App\Services\NevaTravel\NevaOrder;

class NevaTravelCancelOrderListener
{
    public function __construct()
    {
    }

    public function handle(NevaTravelCancelOrderEvent $event): void
    {
        $nevaOrder = new NevaOrder($event->order);
        $nevaOrder->cancel();
    }
}
