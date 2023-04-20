<?php

namespace App\Console\Commands;

use App\NevaTravel\ImportPiers;
use App\NevaTravel\ImportShips;
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
        (new ImportPiers())->run();

        return 0;
    }
}
