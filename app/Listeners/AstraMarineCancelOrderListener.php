<?php

namespace App\Listeners;

use App\Events\AstraMarineCancelOrderEvent;
use App\Services\AstraMarine\AstraMarineOrder;

class AstraMarineCancelOrderListener
{
    public function __construct()
    {
    }

    public function handle(AstraMarineCancelOrderEvent $event): void
    {
        (new AstraMarineOrder($event->order))->cancel();
    }
}
