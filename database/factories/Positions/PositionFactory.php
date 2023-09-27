<?php

namespace Database\Factories\Positions;

use App\Models\Partner\Partner;
use App\Models\Positions\Position;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionFactory extends Factory
{
    protected $model = Position::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {

        return [
            'user_id' => User::factory()->create()->id,
            'partner_id' => Partner::factory()->create()->id,
            'title' => $this->faker->jobTitle,
        ];
    }
}
