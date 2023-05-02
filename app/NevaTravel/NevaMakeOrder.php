<?php

namespace App\NevaTravel;

class NevaMakeOrder
{
    public function run(array $params): array
    {
        $nevaApiData = new NevaTravelRepository();
        return $nevaApiData->makeOrder($params);
    }
}
