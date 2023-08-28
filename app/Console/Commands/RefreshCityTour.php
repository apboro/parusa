<?php

namespace App\Console\Commands;

use App\Services\CityTourBus\ImportTrips;
use Illuminate\Console\Command;

class RefreshCityTour extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'city_tour:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh city tour trips and tickets';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        (new ImportTrips())->run();
        $this->output->info('Successfully done!');
        return 0;
    }
}
