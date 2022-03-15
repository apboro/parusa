<?php

namespace Database\Factories\Piers;

use App\Models\Dictionaries\PiersStatus;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

class PierFactory extends Factory
{
    protected $model = \App\Models\Piers\Pier::class;

    /**
     * Define the model's default state.
     *
     * @return  array
     *
     * @throws Exception
     */
    public function definition(): array
    {
        return [
            'status_id' => PiersStatus::default,
        ];
    }
}
