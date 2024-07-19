<?php

namespace App\Console\Commands;

use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Sails\Trip;
use App\Services\AstraMarine\AstraMarineRepository;
use Illuminate\Console\Command;

class AstraMarineRefreshCommand extends Command
{
    protected $signature = 'astra-marine:refresh';

    protected $description = 'Обновить количество билетов на ближайший день';

    public function handle(): void
    {
        $trips = Trip::query()->with(['additionalData'])
            ->where('provider_id', Provider::astra_marine)
            ->where('start_at', '>', now())
            ->where('end_at', '<', now()->addDay())
            ->get();

        Trip::query()->with(['additionalData'])
            ->where('provider_id', Provider::astra_marine)
            ->where('start_at', '>', now())
            ->where('end_at', '<', now()->addDay())
            ->update([
                'status_id' => TripStatus::cancelled,
                'sale_status_id' => TripSaleStatus::closed_automatically
            ]);

        foreach ($trips as $trip) {
            $response = (new AstraMarineRepository())->getEvents(['eventId' => $trip->additionalData->provider_trip_id])['body'];
            if (!empty($response['events'])) {
                $trip->tickets_total = $response['events'][0]['availableSeats'];
                $trip->sale_status_id = TripSaleStatus::selling;
                $trip->status_id = TripStatus::regular;
                $trip->save();
            }

        }
    }
}
