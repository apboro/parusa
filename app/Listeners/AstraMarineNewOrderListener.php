<?php

namespace App\Listeners;

use App\Events\AstraMarineNewOrderEvent;
use App\Exceptions\AstraMarine\AstraMarineNoTicketException;
use App\Http\APIResponse;
use App\Models\Dictionaries\Provider;
use App\Services\AstraMarine\AstraMarineOrder;

class AstraMarineNewOrderListener
{
    public function __construct()
    {
    }

    /**
     * @throws AstraMarineNoTicketException
     */
    public function handle(AstraMarineNewOrderEvent $event)
    {
        if ($event->order->tickets()->where('provider_id', Provider::astra_marine)->first()) {
            $astraOrder = new AstraMarineOrder($event->order);
            $astraOrder->bookSeats();
            $astraOrder->registerOrder();
        }
    }
}
