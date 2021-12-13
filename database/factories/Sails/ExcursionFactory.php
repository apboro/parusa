<?php

namespace Database\Factories\Sails;

use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Sails\Excursion;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExcursionFactory extends Factory
{
    protected $model = Excursion::class;

    /**
     * Define the model's default state.
     *
     * @return array
     * @throws Exception
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'status_id' => ExcursionStatus::default,
        ];
    }
}
