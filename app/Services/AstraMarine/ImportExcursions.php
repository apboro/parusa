<?php

namespace App\Services\AstraMarine;

use App\Models\Dictionaries\Provider;
use App\Models\Excursions\Excursion;
use App\Models\Integration\AdditionalDataExcursion;

class ImportExcursions
{
    public function run(): void
    {
        $astraApiData = new AstraMarineRepository();
        $astraExcursions = $astraApiData->getServices();

        $excursions = Excursion::with('additionalData')->where('provider_id', Provider::astra_marine)->get();

        foreach ($astraExcursions['body']['services'] as $astraExcursion) {
            $excursion = $excursions->where('additionalData.provider_excursion_id', $astraExcursion['serviceID'])->first();
            if (!$excursion) {
                $newExcursion = new Excursion();
                $newExcursion->setAttribute('name', $astraExcursion['serviceName']);
                $newExcursion->status_id = 2;
                $newExcursion->provider_id = Provider::astra_marine;
                $newExcursion->save();

                $additionalData = new AdditionalDataExcursion();
                $additionalData->excursion_id = $newExcursion->id;
                $additionalData->provider_excursion_id = $astraExcursion['serviceID'];
                $additionalData->provider_id = Provider::astra_marine;
                $additionalData->save();

                $info = $newExcursion->info;
                $info->setAttribute('duration', $astraExcursion['serviceDuration']);
                $info->save();
            }
        }
    }
}
