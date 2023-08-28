<?php

namespace App\Listeners;

use App\Events\NewNevaTravelOrderEvent;
use App\Services\NevaTravel\NevaOrder;

class NewNevaTravelOrderListener
{
    public function __construct()
    {
    }

    public function handle(NewNevaTravelOrderEvent $event): void
    {
        (new NevaOrder($event->order))->make();
    }
}
