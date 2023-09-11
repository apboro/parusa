<?php

namespace App\Services\NevaTravel;


use App\Models\Dictionaries\Provider;
use App\Models\Excursions\Excursion;
use App\Models\Piers\Pier;
use App\Models\Sails\Trip;
use App\Models\Ships\Ship;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;


class ImportTrips
{
    private Carbon $endDate;

    public function __construct(Carbon $endDate)
    {
        $this->endDate = $endDate;
    }

    public function run()
    {
        $nevaApiData = new NevaTravelRepository();
        $piers = Pier::all();
        $ships = Ship::all();
        $excursions = Excursion::query()
            ->with('additionalData')
            ->where('provider_id', 10)
            ->where('status_id', 1)->get();

        $excursionsArray = $excursions->pluck('additionalData.provider_excursion_id')->toArray();

        $currentDate = Carbon::now();

        Trip::query()
            ->where('provider_id', 10)
            ->where('start_at', '>', $currentDate)
            ->where('end_at', '<', $this->endDate->clone()->subHour())
            ->update([
                'status_id' => 4,
                'sale_status_id' => 3,
                'tickets_total' => 0
            ]);

        while ($currentDate <= $this->endDate) {
            $nevaTrips = $nevaApiData->getCruisesInfo([
                'program_ids' => $excursionsArray,
                'departure_date' => $currentDate->format('Y-m-d'),
                'passengers' => 1
            ]);
            if ($nevaTrips) {
                foreach ($nevaTrips['body'] as $nevaTrip) {
                    $ship = $ships->firstWhere('external_id', $nevaTrip['ship_id']);
                    $findExcursion = $excursions->first(function (Excursion $excursion) use ($nevaTrip) {
                        return $excursion->additionalData->provider_excursion_id === $nevaTrip['program_id'];
                    });

                    if ($findExcursion) {
                        $seatsAvailable = $nevaTrip['default_arrival']['prices_table'][0]['available_seats'];

                        $trip = Trip::query()
                            ->whereHas('additionalData', function (Builder $query) use ($nevaTrip) {
                                $query->where('provider_trip_id', $nevaTrip['id']);
                            })->firstOrNew();

                        $trip->start_at = Carbon::parse($nevaTrip['departure_date'])->format('Y-m-d H:i:s');
                        $trip->end_at = Carbon::parse($nevaTrip['default_arrival']['arrival_date'])->format('Y-m-d H:i:s');
                        $trip->excursion_id = $findExcursion->id;
                        $trip->start_pier_id = $piers->firstWhere('external_id', $nevaTrip['pier_id'])->id;
                        $trip->end_pier_id = $piers->firstWhere('external_id', $nevaTrip['default_arrival']['pier_id'])->id;
                        $trip->ship_id = $ship->id;
                        $trip->cancellation_time = 60;
                        $trip->status_id = $seatsAvailable != 'none' ? 1 : 4;
                        $trip->sale_status_id = $seatsAvailable != 'none' ? 1 : 3;
                        $trip->tickets_total = NevaTravelRepository::convertSeatsToInt($seatsAvailable, $ship->capacity);
                        $trip->provider_id = 10;
                        $trip->save();

                        if (!$trip->additionalData) {
                            $trip->additionalData()->create([
                                'provider_id' => Provider::neva_travel,
                                'provider_trip_id' => $nevaTrip['id'],
                                'provider_price_id' => $nevaTrip['default_arrival']['prices_table'][0]['program_price_id']
                            ]);
                        } else {
                            $trip->additionalData()->update([
                                'provider_price_id' => $nevaTrip['default_arrival']['prices_table'][0]['program_price_id']
                            ]);
                        }
                    }
                }
            }
            $currentDate = $currentDate->addHours(24);
        }
    }

}

