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
    private $endDate;

    public function __construct($endDate)
    {
        $this->endDate = $endDate;
    }

    public function run()
    {
        $nevaApiData = new NevaTravelRepository();
        $piers = Pier::all();
        $ships = Ship::all();
        $excursions = Excursion::query()
            ->where('source', 'Нева Трэвел')
            ->where('status_id', 1)->get();
        $excursionsArray = $excursions->pluck('external_id')->toArray();
        $currentDate = Carbon::now();
        $endDate = $this->endDate;

        Trip::query()
            ->where('source', 'NevaTravelApi')
            ->where('start_at', '>', $currentDate)
            ->where('end_at', '<', Carbon::parse($endDate)->subHour())
            ->update(['tickets_total' => 0]);

        while ($currentDate <= $endDate) {
            $nevaTrips = $nevaApiData->getCruisesInfo([
                'program_ids' => $excursionsArray,
                'departure_date' => $currentDate->format('Y-m-d'),
                'passengers' => 1]);
            if ($nevaTrips) {
                foreach ($nevaTrips['body'] as $nevaTrip) {
                    $ship = $ships->firstWhere('external_id', $nevaTrip['ship_id']);
                    if ($excursions->firstWhere('external_id', $nevaTrip['program_id'])) {

                        $trip = Trip::firstOrNew(['external_id' => $nevaTrip['id']]);
                        $trip->start_at = Carbon::parse($nevaTrip['departure_date'])->format('Y-m-d H:i:s');
                        $trip->end_at = Carbon::parse($nevaTrip['default_arrival']['arrival_date'])->format('Y-m-d H:i:s');
                        $trip->excursion_id = $excursions->firstWhere('external_id', $nevaTrip['program_id'])->id;
                        $trip->start_pier_id = $piers->firstWhere('external_id', $nevaTrip['pier_id'])->id;
                        $trip->end_pier_id = $piers->firstWhere('external_id', $nevaTrip['default_arrival']['pier_id'])->id;
                        $trip->ship_id = $ship->id;
                        $trip->cancellation_time = 60;
                        $trip->status_id = $nevaTrip['default_arrival']['prices_table'][0]['available_seats'] != 'none' ? 1 : 4;
                        $trip->sale_status_id = 1;
                        $trip->tickets_total = NevaTravelRepository::convertSeatsToInt(
                            $nevaTrip['default_arrival']['prices_table'][0]['available_seats'],
                            round($ship->capacity * 0.8));
                        $trip->source = 'NevaTravelApi';
                        $trip->program_price_id = $nevaTrip['default_arrival']['prices_table'][0]['program_price_id'];
                        $trip->save();
                    }
                }
            }
            $currentDate = $currentDate->addHours(24);
        }
    }

}

