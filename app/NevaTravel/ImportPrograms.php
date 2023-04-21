<?php

namespace App\NevaTravel;


use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Excursions\Excursion;
use App\Models\Excursions\ExcursionInfo;


class ImportPrograms
{
    public function run()
    {
        $nevaApiData = new NevaTravelRepository();
        $nevaPrograms = $nevaApiData->getProgramsInfo();
        foreach ($nevaPrograms['body'] as $nevaProgram)
            $excursion = Excursion::updateOrCreate(['external_id' => $nevaProgram['id']],
                [
                    'name' => $nevaProgram['name'],
                    'status_id' => $nevaProgram['is_active'] ? 1 : 2,
                ]);
        ExcursionInfo::updateOrCreate(['id' => $excursion->id],
            [
                'duration' => $nevaProgram['full_time'],
            ]);
        ExcursionProgram::updateOrCreate(['excursion_id' => $excursion->id],
            [
                'program_id'=>17
            ]);
    }

}
