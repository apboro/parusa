<?php

namespace App\Services\AstraMarine;

use App\Models\Dictionaries\Provider;
use App\Models\Excursions\Excursion;
use App\Models\Piers\Pier;
use App\Models\Sails\Trip;
use App\Models\Ships\Ship;
use Carbon\Carbon;

class ImportTrips
{

    public function run(): void
    {
        $astraApiData = new AstraMarineRepository();
        $trips = Trip::query()
            ->with(['additionalData'])
            ->where('start_at', '>=', now())
            ->where('provider_id', Provider::astra_marine)
            ->get();
        $excursions = Excursion::query()
            ->with('additionalData')
            ->where('provider_id', Provider::astra_marine)
            ->where('status_id', 1)->get();
        $piers = Pier::query()
            ->where('provider_id', Provider::astra_marine)
            ->get();
        $ships = Ship::query()
            ->where('provider_id', Provider::astra_marine)
            ->get();

        if ($excursions->isEmpty())
            return;

        $astraTrips = $astraApiData->getEvents(["dateFrom" => now()->toIso8601String()]);

        foreach ($astraTrips['body']['events'] as $astraTrip) {
            $findExcursion = $excursions->first(function (Excursion $excursion) use ($astraTrip) {
                return $excursion->additionalData->provider_excursion_id === $astraTrip['serviceID'];
            });

            if ($findExcursion) {
                $trip = $trips->where('additionalData.provider_trip_id', $astraTrip['eventID'])->first();
                if (!$trip) {
                    $trip = new Trip();
                    $trip->start_at = Carbon::parse($astraTrip['eventDateTime']);
                    $trip->end_at = Carbon::parse($astraTrip['eventDateTime'])->addMinutes($astraTrip['eventDuration']);
                    $trip->excursion_id = $findExcursion->id;
                    $trip->start_pier_id = $piers->firstWhere('external_id', $astraTrip['pierID'])->id ?? $this->importPier($astraTrip);
                    $trip->end_pier_id = $piers->firstWhere('external_id', $astraTrip['endPointID'])->id ?? $this->importPier($astraTrip);
                    $trip->ship_id = $ships->firstWhere('external_id', $astraTrip['venueID'])->id ?? $this->importShip($astraTrip);
                    $trip->cancellation_time = 60;
                    $trip->provider_id = Provider::astra_marine;
                }
                $trip->status_id = $astraTrip['availableSeats'] > 0 ? 1 : 4;
                $trip->sale_status_id = $astraTrip['availableSeats'] > 0 ? 1 : 3;
                $trip->tickets_total = $astraTrip['availableSeats'];
                $trip->save();
            }
        }
    }

    public
    function importPier(array $data): int
    {
        $pier = Pier::create([
            'name' => $data['pierName'],
            'external_id' => $data['pierId'],
            'provider_id' => Provider::astra_marine,
        ]);

        $info = $pier->info;
        $info->address = explode('|', $data['pierName'])[0] ?? $data['pierName'];
        $info->save();

        return $pier->id;
    }

    public
    function importShip(array $data): int
    {
        $ship = Ship::create([
            'name' => ucfirst($data['venueName']),
            'status_id' => 1,
            'owner' => 'Astra Marine',
            'capacity' => 110,
            'external_id' => $data['venueID'],
            'provider_id' => Provider::neva_travel
        ]);

        return $ship->id;
    }
}
