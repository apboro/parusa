<?php

namespace App\NevaTravel;


use App\Http\APIResponse;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Excursions\Excursion;
use App\Models\Excursions\ExcursionInfo;


class ImportPrograms
{
    public function run()
    {
        $nevaApiData = new NevaTravelRepository();
        $nevaPrograms = $nevaApiData->getProgramsInfo();
        foreach ($nevaPrograms['body'] as $nevaProgram) {
            $excursion = Excursion::firstOrNew(['external_id'=> $nevaProgram['id']]);
            $excursion->setAttribute('name', $nevaProgram['name']);
            $excursion->source = 'Нева Трэвел';

            if ($excursion->status_id == 1 && !$excursion->neva_status && $nevaProgram['is_active']) {
                $excursion->status_id = 2;
                $excursion->neva_status = true;
            }
            if ($excursion->status_id == 1 && $excursion->neva_status && !$nevaProgram['is_active']) {
                $excursion->neva_status = false;
            }
            if ($excursion->status_id == 2 && !$excursion->neva_status && !$nevaProgram['is_active']) {
                $excursion->status_id = 1;
            }
            if ($excursion->status_id == 2 && $excursion->neva_status && !$nevaProgram['is_active']) {
                $excursion->neva_status = false;
                $excursion->status_id = 1;
            }
            if ($excursion->status_id == 2 && !$excursion->neva_status && $nevaProgram['is_active']) {
                $excursion->neva_status = true;
            }

            $excursion->save();

            $info = $excursion->info;
            $info->setAttribute('duration', $nevaProgram['full_time']);
            $info->save();

            $excursion->programs()->sync(17);
        }
    }

}
