<?php

namespace Database\Factories\Sails;

use App\Models\Dictionaries\Provider;
use App\Models\Excursions\Excursion;
use App\Models\Piers\Pier;
use App\Models\Ships\Ship;
use Illuminate\Database\Eloquent\Factories\Factory;

class TripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'start_at' => now()->addHours(),
            'end_at' => now()->addHours(3),
            'start_pier_id' => Pier::factory()->create()->id,
            'end_pier_id' => Pier::factory()->create()->id,
            'ship_id' => Ship::factory()->create()->id,
            'excursion_id' => Excursion::factory()->create()->id,
            'status_id' => 1,
            'sale_status_id' => 1,
            'tickets_total' => 1,
            'discount_status_id' => 1,
            'cancellation_time' => 100,
            'provider_id' => Provider::inRandomOrder()->first()->id,
        ];
    }
}
