<?php

namespace App\Listeners;

use App\Events\NevaTravelOrderPaidEvent;
use App\Services\NevaTravel\NevaOrder;
use Illuminate\Support\Facades\Log;


class NevaTravelOrderPaidListener
{
    public function __construct()
    {
    }

    public function handle(NevaTravelOrderPaidEvent $event): void
    {
        Log::channel('neva')->debug('call listener neva paid order', [$event]);
        (new NevaOrder($event->order))->approve();
    }
}
