<?php

namespace App\Console\Commands;

use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Sails\Trip;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessTrips extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:trips';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically change trip statuses';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $now = Carbon::now();

        Trip::query()
            ->where('status_id', TripStatus::regular)
            ->where('start_at', '<=', $now)
            ->update(['status_id' => TripStatus::processing]);

        Trip::query()
            ->where('sale_status_id', TripSaleStatus::selling)
            ->where('start_at', '<=', $now)
            ->update(['sale_status_id' => TripSaleStatus::closed_automatically]);

        $tripsQuery = Trip::query()
            ->where('status_id', TripStatus::processing)
            ->where('end_at', '<=', $now);

        $trips = $tripsQuery->get();
        foreach ($trips ?? [] as $trip){
            foreach($trip->tickets ?? [] as $ticket){
                if(in_array($ticket->status_id, TicketStatus::ticket_single_statuses)){
                    $nextTrip = $trip->getAllTripsOfExcursionAndPierOnDay()->orderBy('start_at')->first();
                    if ($nextTrip) {
                        $ticket->trip_id = $nextTrip->id;
                        $ticket->save();
                    }
                }
            }
        }
        $tripsQuery->update(['status_id' => TripStatus::finished]);

        return 0;
    }
}
