<?php

namespace Database\Factories\Positions;

use App\Models\Positions\Position;
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
            'title' => $this->faker->jobTitle,
        ];
    }
}
