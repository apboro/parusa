<?php

namespace App\NevaTravel;


use App\Http\APIResponse;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Excursions\Excursion;
use App\Models\Excursions\ExcursionInfo;
use App\Models\ProviderExcursion;


class ImportPrograms
{
    public function run()
    {
        $nevaApiData = new NevaTravelRepository();
        $nevaPrograms = $nevaApiData->getProgramsInfo();
        $excursions = Excursion::with('providerExcursion')->where('provider_id', 10)->get();
        foreach ($nevaPrograms['body'] as $nevaProgram) {
            $foundExcursion = null;
            foreach ($excursions as $excursion) {
                if ($excursion->providerExcursion->provider_excursion_id === $nevaProgram['id']) {
                    $foundExcursion = $excursion;
                    break;
                }
            }
            if ($foundExcursion){
                $foundExcursion->providerExcursion->update(['provider_excursion_status'=>$nevaProgram['is_active'] ? 1 : 0]);

                $info = $foundExcursion->info;
            } else {
                $newExcursion = new Excursion();
                $newExcursion->setAttribute('name', $nevaProgram['name']);
                $newExcursion->status_id = 2;
                $newExcursion->provider_id = 10;
                $newExcursion->save();

                $providerExcursion = new ProviderExcursion();
                $providerExcursion->excursion_id = $newExcursion->id;
                $providerExcursion->provider_excursion_id = $nevaProgram['id'];
                $providerExcursion->provider_id = 10;
                $providerExcursion->provider_excursion_status = $nevaProgram['is_active'];
                $providerExcursion->save();

                $info = $newExcursion->info;
            }
            $info->setAttribute('duration', $nevaProgram['full_time']);
            $info->save();
        }
    }

}
