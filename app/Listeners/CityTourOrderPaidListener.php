<?php

namespace App\Listeners;

use App\Events\CityTourOrderPaidEvent;
use App\Services\CityTourBus\CityTourOrder;

class CityTourOrderPaidListener
{
    public function __construct()
    {
    }

    public function handle(CityTourOrderPaidEvent $event): void
    {
        $cityTourOrder = new CityTourOrder($event->order);
        $cityTourOrder->approve();
        $cityTourOrder->sendTickets();
    }
}
