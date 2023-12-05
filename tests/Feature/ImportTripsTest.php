<?php

namespace Tests\Feature;

use App\Services\AstraMarine\AstraMarineRepository;
use Tests\TestCase;

class ImportTripsTest extends TestCase
{
    public function test_astra_marine_api()
    {
        $astraApiData = new AstraMarineRepository();

        $this->assertEquals('200', $astraApiData->getServices()['status']);

    }
}
