<?php

namespace App\Listeners;

use App\Events\CityTourCancelOrderEvent;
use App\Events\CityTourOrderPaidEvent;
use App\Services\CityTourBus\CityTourOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CityTourCancelOrderListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(CityTourCancelOrderEvent $event)
    {
        $cityTourOrder = new CityTourOrder($event->order);
        $cityTourOrder->cancel();
    }
}
