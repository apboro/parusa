<?php

namespace Database\Factories\Positions;

use App\Models\Positions\StaffPositionInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffPositionInfoFactory extends Factory
{
    protected $model = StaffPositionInfo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {

        return [
            'email' => $this->faker->email,
            'work_phone' => $this->faker->numerify('+7 (###) ###-##-##'),
            'mobile_phone' => $this->faker->numerify('+7 (###) ###-##-##'),
        ];
    }
}
