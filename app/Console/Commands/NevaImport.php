<?php

namespace App\Console\Commands;

use App\NevaTravel\ImportPiers;
use App\NevaTravel\ImportPrograms;
use App\NevaTravel\ImportProgramsPrices;
use App\NevaTravel\ImportShips;
use App\NevaTravel\ImportTrips;
use Illuminate\Console\Command;

class NevaImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'neva:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Neva Travel Entities';

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
        (new ImportShips())->run();
        $this->info('Ships imported');
        (new ImportPiers())->run();
        $this->info('Piers imported');
        (new ImportPrograms())->run();
        $this->info('Programs imported');

        (new ImportProgramsPrices())->run();
        $this->info('Prices imported');

//        (new ImportTrips())->run();
//        $this->info('Trips imported');

        return 0;
    }
}
