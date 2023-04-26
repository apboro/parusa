<?php

namespace App\NevaTravel;

use App\Models\Piers\Pier;
use App\Models\Piers\PierInfo;

class ImportPiers
{
    public function run()
    {
        $nevaApiData = new NevaTravelRepository();
        $nevaPiers = $nevaApiData->getPiersInfo();
        foreach ($nevaPiers['body'] as $nevaPier) {
            $pier = Pier::updateOrCreate(['external_id' => $nevaPier['id']],
                [
                    'external_parent_id' => $nevaPier['parent_id'],
                    'name' => $nevaPier['name'],
                    'status_id' => $nevaPier['is_active'] ? 1 : 2,
                    'source'=>'NevaTravelApi',
                ]);
            PierInfo::updateOrCreate(['pier_id' => $pier->id],
                [
                    'address' => $nevaPier['address'],
                    'label' => $nevaPier['label'],
                    'description' => $nevaPier['description'] ?? null,
                ]);
        }
    }

}
