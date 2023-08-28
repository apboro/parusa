<?php

namespace App\Services\CityTourBus;

use App\Models\Dictionaries\Provider;
use App\Models\Excursions\Excursion;
use App\Models\Piers\Pier;
use App\Models\Sails\Trip;
use App\Models\Ships\Ship;
use Illuminate\Support\Carbon;

class ImportTrips
{
    public function run()
    {
        $rep = new CityTourRepository();

        $pier = Pier::where('source', 'CityTour')->first();
        $bus = Ship::where('owner', 'CityTour')->first();
        $excursions = Excursion::with('additionalData')->where('provider_id', Provider::city_tour)->get();

        foreach ($excursions as $excursion) {
            $schedule = $rep->getSchedule($excursion->additionalData->provider_excursion_id);
            if (isset($schedule['body']['schedule_state']) && $schedule['body']['schedule_state'] === 'free') {
                $excursion->update(['is_single_ticket' => 1]);

                $start_date = Carbon::today();

                do {
                    $trip = Trip::firstOrNew([
                        'excursion_id' => $excursion->id,
                        'start_at' => $start_date->clone()->setTime(9,0)->format('Y-m-d H:i:s')
                    ]);
                    $trip->end_at = $start_date->clone()->setTime(21,50)->format('Y-m-d H:i:s');
                    $trip->excursion_id = $excursion->id;
                    $trip->start_pier_id = $pier->id;
                    $trip->end_pier_id = $pier->id;
                    $trip->ship_id = $bus->id;
                    $trip->cancellation_time = 60;
                    $trip->status_id = 1;
                    $trip->sale_status_id = 1;
                    $trip->tickets_total = 100;
                    $trip->provider_id = Provider::city_tour;
                    $trip->save();

                } while ($start_date->addDay() < Carbon::now()->addDays(60));

            } else {
                foreach ($schedule['body'] as $date => $timeTickets) {

                    $time = array_key_first($timeTickets);
                    $trip = Trip::firstOrNew(['excursion_id' => $excursion->id, 'start_at' => Carbon::parse($date.' '.$time)->format('Y-m-d H:i:s')]);
                    $trip->start_at = Carbon::parse($date.' '.$time)->format('Y-m-d H:i:s');
                    $trip->end_at = Carbon::parse($date.' '.$time)->addHours(7)->format('Y-m-d H:i:s');
                    $trip->excursion_id = $excursion->id;
                    $trip->start_pier_id = $pier->id;
                    $trip->end_pier_id = $pier->id;
                    $trip->ship_id = $bus->id;
                    $trip->cancellation_time = 60;
                    $trip->status_id = 1;
                    $trip->sale_status_id = 1;
                    $trip->tickets_total = reset($timeTickets);
                    $trip->provider_id = Provider::city_tour;
                    $trip->save();
                }
            }
        }
    }

}
