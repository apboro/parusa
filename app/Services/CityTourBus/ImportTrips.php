<?php

namespace App\Services\CityTourBus;

use App\Models\Dictionaries\Provider;
use App\Models\Excursions\Excursion;
use App\Models\Piers\Pier;
use App\Models\Sails\Trip;
use App\Models\Ships\Ship;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ImportTrips
{
    public function run()
    {
        try {
            $rep = new CityTourRepository();
            $pier = Pier::where('provider_id', Provider::city_tour)->first();
            $bus = Ship::where('owner', 'CityTour')->first();
            $excursions = Excursion::with('additionalData')->where('provider_id', Provider::city_tour)->get();
            Trip::query()
                ->where('provider_id', Provider::city_tour)
                ->update(['status_id' => 4, 'sale_status_id' => 3]);
            foreach ($excursions as $excursion) {
                $schedule = $rep->getSchedule($excursion->additionalData->provider_excursion_id);
                if (isset($schedule['body']['schedule_state']) && $schedule['body']['schedule_state'] === 'free') {
                    $excursion->update(['is_single_ticket' => 1]);

                    $start_date = Carbon::today();

                    do {
                        $trip = Trip::firstOrNew([
                            'excursion_id' => $excursion->id,
                            'start_at' => $start_date->clone()->setTime(9, 0)->format('Y-m-d H:i:s')
                        ]);
                        $trip->end_at = $start_date->clone()->setTime(21, 50)->format('Y-m-d H:i:s');
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
                    } while ($start_date->addDay() < Carbon::now()->addDays(10));
                } elseif ($schedule['status'] === 200) {
                    foreach ($schedule['body'] as $date => $timeTickets) {
                        $time = array_key_first($timeTickets);
                        $trip = Trip::firstOrNew(['excursion_id' => $excursion->id, 'start_at' => Carbon::parse($date . ' ' . $time)->format('Y-m-d H:i:s')]);
                        $trip->start_at = Carbon::parse($date . ' ' . $time)->format('Y-m-d H:i:s');
                        $trip->end_at = Carbon::parse($date . ' ' . $time)->addHours(7)->format('Y-m-d H:i:s');
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
        } catch (\Exception $e) {
            Log::channel('city_tour')->error($e->getMessage());
        }
    }

}
