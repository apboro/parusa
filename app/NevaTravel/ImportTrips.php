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

        while ($currentDate <= $endDate) {
            $nevaTrips = $nevaApiData->getCruisesInfo(['program_ids' => $excursionsArray, 'departure_date' => $currentDate->format('Y-m-d')]);
            if ($nevaTrips) {
                foreach ($nevaTrips['body'] as $nevaTrip) {
                    $ship = $ships->firstWhere('external_id', $nevaTrip['ship_id']);
                    if ($excursions->firstWhere('external_id', $nevaTrip['program_id'])) {
                        Trip::updateOrCreate(['external_id' => $nevaTrip['id']],
                            [
                                'start_at' => Carbon::parse($nevaTrip['departure_date'])->format('Y-m-d H:i:s'),
                                'end_at' => Carbon::parse($nevaTrip['default_arrival']['arrival_date'])->format('Y-m-d H:i:s'),
                                'excursion_id' => $excursions->firstWhere('external_id', $nevaTrip['program_id'])->id,
                                'start_pier_id' => $piers->firstWhere('external_id', $nevaTrip['pier_id'])->id,
                                'end_pier_id' => $piers->firstWhere('external_id', $nevaTrip['default_arrival']['pier_id'])->id,
                                'ship_id' => $ship->id,
                                'cancellation_time' => 60,
                                'status_id' => $nevaTrip['default_arrival']['prices_table'][0]['available_seats'] != 'none' ? 1 : 4,
                                'sale_status_id' => 1,
                                'tickets_total' => round($ship->capacity * 0.8),
                                'source' => 'NevaTravelApi',
                                'program_price_id' => $nevaTrip['default_arrival']['prices_table'][0]['program_price_id'],
                            ]);
                    }
                }
            }
            $currentDate = $currentDate->addDay();
        }
    }

}
