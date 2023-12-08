<?php

namespace App\Listeners;

use App\Events\AstraMarineOrderPaidEvent;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\Provider;
use App\Services\AstraMarine\AstraMarineOrder;

class AstraMarineOrderPaidListener
{
    public function __construct()
    {
    }

    public function handle(AstraMarineOrderPaidEvent $event): void
    {
        if ($event->order->tickets()->where('provider_id', Provider::astra_marine)->first()) {
            if (in_array($event->order->status_id, OrderStatus::order_printable_statuses)) {
                $astraOrder = new AstraMarineOrder($event->order);
                $astraOrder->confirmOrder();
            }
        }
    }
}
