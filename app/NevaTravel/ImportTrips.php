<?php

namespace App\NevaTravel;


use App\Models\Excursions\Excursion;
use App\Models\Piers\Pier;
use App\Models\Sails\Trip;
use App\Models\Ships\Ship;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Laravel\Telescope\Telescope;


class ImportTrips
{
    public function run()
    {
        $nevaApiData = new NevaTravelRepository();

        $meteors = Ship::whereNotNull('external_id')->pluck('external_id');
        $piers = Pier::all();
        $ships = Ship::all();
        $excursions = Excursion::all();

        for ($i = 0; $i <= 5; $i++) {

            $nevaTrips = $nevaApiData->getCruisesInfo(['departure_date' => now()->addDays($i)->format('Y-m-d')]);
            foreach ($nevaTrips['body'] as $nevaTrip) {

                if ($meteors->contains($nevaTrip['ship_id'])) {

                    $ship = $ships->firstWhere('external_id', $nevaTrip['ship_id']);

                    Trip::updateOrCreate(['external_id' => $nevaTrip['id']],
                        [
                            'start_at' => Carbon::parse($nevaTrip['departure_date'])->format('Y-m-d H:i:s'),
                            'end_at' => Carbon::parse($nevaTrip['default_arrival']['arrival_date'])->format('Y-m-d H:i:s'),
                            'excursion_id' => $excursions->firstWhere('external_id', $nevaTrip['program_id'])->id,
                            'start_pier_id' => $piers->firstWhere('external_id', $nevaTrip['pier_id'])->id,
                            'end_pier_id' => $piers->firstWhere('external_id', $nevaTrip['default_arrival']['pier_id'])->id,
                            'ship_id' => $ship->id,
                            'cancellation_time' => 60,
                            'status_id' => 1,
                            'sale_status_id' => 1,
                            'tickets_total' => round($ship->capacity * 0.8),
                            'external_id' => $nevaTrip['id'],
                            'source' => 'NevaTravelApi',
                            'program_price_id' => $nevaTrip['default_arrival']['prices_table'][0]['program_price_id'],
                        ]);
                }
            }
        }
    }

}
