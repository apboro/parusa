<?php

namespace App\Console;

use App\Console\Commands\CloseWorkShiftsCommand;
use App\Console\Commands\GetCityTourScheduleCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
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
        $schedule->command('get:city-tour-schedule')->hourly();
        $schedule->command('close:work-shifts')->dailyAt('1:05');
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
