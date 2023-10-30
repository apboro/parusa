<?php

namespace App\Services\NevaTravel;


use App\Models\Dictionaries\Provider;
use App\Models\Excursions\Excursion;
use App\Models\Integration\AdditionalDataExcursion;


class ImportPrograms
{
    public function run()
    {
        $nevaApiData = new NevaTravelRepository();
        $nevaPrograms = $nevaApiData->getProgramsInfo();
        $excursions = Excursion::with('additionalData')->where('provider_id', 10)->get();
        foreach ($nevaPrograms['body'] as $nevaProgram) {
            $foundExcursion = null;
            foreach ($excursions as $excursion) {
                if ($excursion->additionalData?->provider_excursion_id === $nevaProgram['id']) {
                    $foundExcursion = $excursion;
                    break;
                }
            }
            if ($foundExcursion){
                $foundExcursion->additionalData->update(['provider_excursion_status'=>$nevaProgram['is_active'] ? 1 : 0]);

                $info = $foundExcursion->info;
            } else {
                $newExcursion = new Excursion();
                $newExcursion->setAttribute('name', $nevaProgram['name']);
                $newExcursion->status_id = 2;
                $newExcursion->provider_id = Provider::neva_travel;
                $newExcursion->save();

                $additionalData = new AdditionalDataExcursion();
                $additionalData->excursion_id = $newExcursion->id;
                $additionalData->provider_excursion_id = $nevaProgram['id'];
                $additionalData->provider_id = Provider::neva_travel;
                $additionalData->provider_excursion_status = $nevaProgram['is_active'];
                $additionalData->save();

                $info = $newExcursion->info;
            }
            $info->setAttribute('duration', $nevaProgram['full_time']);
            $info->save();
        }
    }

}
