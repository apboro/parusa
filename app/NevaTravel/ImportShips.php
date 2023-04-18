<?php

namespace App\NevaTravel;


use App\Models\Ships\Ship;
use Illuminate\Support\Str;

class ImportShips
{
    public function run()
    {
        $nevaApiData = new NevaTravelRepository();
        $nevaShips = $nevaApiData->getShipsInfo();
        foreach ($nevaShips['body'] as $nevaShip) {
            if (Str::contains($nevaShip['name'], 'Метеор')) {
                Ship::updateOrCreate(['external_id' => $nevaShip['id']],
                    [
                        'name' => $nevaShip['name'],
                        'enabled' => $nevaShip['is_active'] ? 1 : 0,
                        'order' => Ship::max('order') + 1,
                        'status_id' => $nevaShip['is_active'] ? 1 : 2,
                        'owner' => 'Нева Трэвэл',
                        'capacity' => $nevaShip['capacity'],
                        'label' => $nevaShip['label'],
                        'source'=>'nevaTravelApi,'
                    ]);
            }
        }
    }

}
