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
            $excursion = Excursion::firstOrCreate(['external_id' => $nevaProgram['id']]);
            if ($excursion->wasRecentlyCreated) {
                $excursion->setAttribute('name', $nevaProgram['name']);
                $excursion->neva_status = $nevaProgram['is_active'];
                $excursion->status_id = 2;
            }
            $excursion->source = 'Нева Трэвел';

            if ($nevaProgram['is_active'] === false) {
                $excursion->status_id = 2;
                $excursion->neva_status = false;
            }

            $excursion->save();

            $info = $excursion->info;
            $info->setAttribute('duration', $nevaProgram['full_time']);
            $info->save();

        }
    }

}
