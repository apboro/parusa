<?php

namespace App\Listeners;

use App\Events\NevaTravelOrderPaidEvent;
use App\Services\NevaTravel\NevaOrder;


class NevaTravelOrderPaidListener
{
    public function __construct()
    {
    }

    public function handle(NevaTravelOrderPaidEvent $event): void
    {
        (new NevaOrder($event->order))->approve();
    }
}
