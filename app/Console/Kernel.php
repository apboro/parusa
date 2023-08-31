<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('process:trips')->everyMinute();
        $schedule->command('process:orders')->everyMinute();
        $schedule->command('lifepos:sync')->dailyAt('4:00');
        $schedule->command('sync:showcase_pay_waiting')->everyMinute();
        $schedule->command('city_tour:import')->weekly();
        if (config('app.env') === 'production') {
            $schedule->command('city_tour:refresh')->everyFifteenMinutes();
            $schedule->command('neva:today')->everyFiveMinutes();
            $schedule->command('neva:import')->dailyAt('5:00');
        } else {
            $schedule->command('neva:today')->everySixHours();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
