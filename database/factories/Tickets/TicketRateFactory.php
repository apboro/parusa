<?php

namespace Database\Factories\Tickets;

use App\Models\Tickets\TicketRate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TicketRateFactory extends Factory
{
    protected $model = TicketRate::class;

    public function definition(): array
    {
        return [
            'rate_id' => $this->faker->randomNumber(),
            'grade_id' => $this->faker->randomNumber(),
            'base_price' => 1000,
            'min_price' => 800,
            'max_price' => 1200,
            'commission_type' => $this->faker->word(),
            'commission_value' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'backward_price_type' => $this->faker->word(),
            'backward_price_value' => $this->faker->randomNumber(),
            'partner_price' => $this->faker->randomNumber(),
        ];
    }
}
