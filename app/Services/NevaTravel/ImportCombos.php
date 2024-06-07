<?php

namespace App\Services\NevaTravel;

use App\Models\Combo;
use App\Services\NevaTravel\NevaTravelRepository;

class ImportCombos
{
    public function run()
    {
        $nevaApiData = new NevaTravelRepository();
        $combos = $nevaApiData->getCombosInfo()['body'];
        foreach ($combos as $combo) {
            Combo::updateOrCreate(
                ['combo_id' => $combo['id']],
                ['combo' => $combo]);
        }
    }
}
