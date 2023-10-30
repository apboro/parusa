<?php

namespace Tests\Unit;

use App\Services\CityTourBus\CityTourRepository;
use Tests\TestCase;

class CityTourConnectionTest extends TestCase
{
    private CityTourRepository $cityTourRepository;
    public function setUp(): void
    {
        parent::setUp();
        $this->cityTourRepository = new CityTourRepository();
    }

    public function test_city_tour_api_available()
    {
        $this->assertEquals(200, $this->cityTourRepository->getExcursions()['status']);
    }
}
