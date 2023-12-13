<?php

namespace App\Listeners;

use App\Events\AstraMarineCancelOrderEvent;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\Provider;
use App\Services\AstraMarine\AstraMarineOrder;

class AstraMarineCancelOrderListener
{
    public function __construct()
    {
    }

    public function handle(AstraMarineCancelOrderEvent $event): void
    {
        if ($event->order->tickets()->where('provider_id', Provider::astra_marine)->first()) {
            if (in_array($event->order->status_id, OrderStatus::order_printable_statuses)) {
                (new AstraMarineOrder($event->order))->cancel();
            }
        }
    }
}
