<?php

namespace App\Console\Commands;

use App\Services\NevaTravel\ImportCombos;
use App\Services\NevaTravel\ImportPiers;
use App\Services\NevaTravel\ImportPrograms;
use App\Services\NevaTravel\ImportProgramsPrices;
use App\Services\NevaTravel\ImportShips;
use App\Services\NevaTravel\ImportTrips;
use Carbon\Carbon;
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

        (new ImportCombos())->run();
        $this->info('Combos imported');

        $endDate = Carbon::now()->setDay(1)->month(12);
        (new ImportTrips($endDate))->run();
        $this->info('Trips imported');

        return 0;
    }
}
