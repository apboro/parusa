<?php

namespace Database\Factories\Partner;

use App\Models\Partner\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerFactory extends Factory
{
    protected $model = Partner::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->jobTitle,
            'type_id' => 1000,
        ];
    }
}
