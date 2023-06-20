<?php

namespace App\Console\Commands;

use App\NevaTravel\ImportTrips;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RefreshNevaTodayTrips extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'neva:today';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh today Neva Trips';

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
        $endDate = Carbon::now()->addHours(25);
        (new ImportTrips($endDate))->run();
        $this->info('Trips imported for '.$endDate->format('Y-m-d'));
        return 0;
    }
}
