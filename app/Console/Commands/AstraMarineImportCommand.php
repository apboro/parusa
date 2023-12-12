<?php

namespace App\Console\Commands;

use App\Services\AstraMarine\ImportExcursions;
use App\Services\AstraMarine\ImportTrips;
use Illuminate\Console\Command;

class AstraMarineImportCommand extends Command
{
    protected $signature = 'astra-marine:import';

    protected $description = 'Import Astra Marine Data';

    public function handle(): void
    {
        (new ImportExcursions())->run();
        $this->info('excursions imported');
        (new ImportTrips())->run();
        $this->info('trips imported');
    }
}
