<?php

namespace App\Listeners;

use App\Events\NewCityTourOrderEvent;
use App\Services\CityTourBus\CityTourOrder;

class NewCityTourOrderListener
{
    public function __construct()
    {
    }

    public function handle(NewCityTourOrderEvent $event): void
    {
        $cityTourOrder = new CityTourOrder($event->order);
        $cityTourOrder->make();
    }
}
