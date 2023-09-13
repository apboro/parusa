<?php

namespace App\Services\NevaTravel;

use App\Models\Dictionaries\Provider;
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
                    'external_parent_id' => $nevaPier['parent_id'] ?? null,
                    'name' => $nevaPier['name'],
                    'status_id' => $nevaPier['is_active'] ? 1 : 2,
                    'provider_id'=> Provider::neva_travel,
                ]);
            PierInfo::updateOrCreate(['pier_id' => $pier->id],
                [
                    'address' => $nevaPier['address'],
                    'label' => $nevaPier['label'],
                    'description' => $nevaPier['description'] ?? null,
                ]);
            if ($nevaPier['parent_id'] != ''){
                $parentPierName = $nevaApiData->getPiersInfo(['pier_ids'=>$nevaPier['parent_id']])['body'][0]['name'];
                $pier->name=$parentPierName.' '. $pier->name;
                $pier->save();
            }
        }
    }

}
