<?php

namespace App\Console\Commands;

use App\Services\CityTourBus\ImportExcursionsAndRates;
use App\Services\CityTourBus\ImportTrips;
use Illuminate\Console\Command;

class ImportCityTour extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'city_tour:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        (new ImportExcursionsAndRates())->run();
        (new ImportTrips())->run();
        $this->output->info('Successfully done!');
        return 0;
    }
}
