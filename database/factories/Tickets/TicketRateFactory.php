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
            'base_price' => $this->faker->randomNumber(),
            'min_price' => $this->faker->randomNumber(),
            'max_price' => $this->faker->randomNumber(),
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
