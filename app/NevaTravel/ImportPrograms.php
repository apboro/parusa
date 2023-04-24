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
            $excursion->status_id = $nevaProgram['is_active'] ? 1 : 2;
            $excursion->save();

            $info = $excursion->info;
            $info->setAttribute('duration', $nevaProgram['full_time']);
            $info->save();

            $excursion->programs()->sync(17);
        }
    }

}
