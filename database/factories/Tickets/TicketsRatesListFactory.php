<?php

namespace Database\Factories\Tickets;

use App\Models\Excursions\Excursion;
use App\Models\Tickets\TicketsRatesList;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TicketsRatesListFactory extends Factory
{
    protected $model = TicketsRatesList::class;

    public function definition(): array
    {
        return [
            'start_at' => Carbon::now()->addMinutes(5),
            'end_at' => Carbon::now()->addHours(2),
            'excursion_id' => Excursion::factory()->create()->id,
            'caption' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
