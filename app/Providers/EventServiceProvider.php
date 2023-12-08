<?php

namespace App\Providers;

use App\Events\AstraMarineNewOrderEvent;
use App\Events\AstraMarineOrderPaidEvent;
use App\Events\CityTourCancelOrderEvent;
use App\Events\CityTourOrderPaidEvent;
use App\Events\NevaTravelCancelOrderEvent;
use App\Events\NevaTravelOrderPaidEvent;
use App\Events\NewCityTourOrderEvent;
use App\Events\NewNevaTravelOrderEvent;
use App\Listeners\AstraMarineNewOrderListener;
use App\Listeners\AstraMarineOrderPaidListener;
use App\Listeners\CityTourCancelOrderListener;
use App\Listeners\CityTourOrderPaidListener;
use App\Listeners\NevaTravelCancelOrderListener;
use App\Listeners\NevaTravelOrderPaidListener;
use App\Listeners\NewCityTourOrderListener;
use App\Listeners\NewNevaTravelOrderListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        NevaTravelCancelOrderEvent::class => [
            NevaTravelCancelOrderListener::class,
        ],
        NewCityTourOrderEvent::class => [
            NewCityTourOrderListener::class,
        ],
        CityTourOrderPaidEvent::class => [
            CityTourOrderPaidListener::class,
        ],
        CityTourCancelOrderEvent::class => [
            CityTourCancelOrderListener::class,
        ],
        NevaTravelOrderPaidEvent::class => [
            NevaTravelOrderPaidListener::class,
        ],
        NewNevaTravelOrderEvent::class => [
            NewNevaTravelOrderListener::class,
        ],
        AstraMarineNewOrderEvent::class => [
            AstraMarineNewOrderListener::class,
        ],
        AstraMarineOrderPaidEvent::class => [
            AstraMarineOrderPaidListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
