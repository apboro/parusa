<?php

namespace App\NevaTravel;


use App\Http\APIResponse;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Excursions\Excursion;
use App\Models\Excursions\ExcursionInfo;
use App\Models\Sails\Trip;
use App\Models\Ships\Ship;
use Carbon\Carbon;


class ImportTrips
{
    public function run()
    {
        $nevaApiData = new NevaTravelRepository();
        $nevaTrips = $nevaApiData->getSchedule([
            'departure_time_from'=>'2023-04-25T15:30:00.000Z',
            'departure_time_to'=>'2023-04-30T15:30:00.000Z'
        ]);

        $meteors = Ship::whereNotNull('external_id')->pluck('external_id');

        foreach ($nevaTrips['body'] as $nevaTrip) {
            $excursion = Excursion::where('external_id', $nevaTrip['program_id'])->first();
            if ($meteors->contains($nevaTrip['ship_id'])){
                Trip::createOrUpdate(['external_id'=>$nevaTrip['id']],
                [
                    'start_at'=>$nevaTrip['departure_time'],

                ]);
            }
        }
    }

}
