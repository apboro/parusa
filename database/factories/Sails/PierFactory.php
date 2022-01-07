<?php

namespace Database\Factories\Sails;

use App\Models\Dictionaries\PiersStatus;
use App\Models\Sails\Pier;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

class PierFactory extends Factory
{
    protected $model = Pier::class;

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
