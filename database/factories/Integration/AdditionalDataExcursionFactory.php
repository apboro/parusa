<?php

namespace Database\Factories\Integration;

use App\Models\Dictionaries\Provider;
use App\Models\Excursions\Excursion;
use App\Models\Integration\AdditionalDataExcursion;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdditionalDataExcursionFactory extends Factory
{
    protected $model = AdditionalDataExcursion::class;

    public function definition(): array
    {
        return [
            'provider_id' => Provider::inRandomOrder()->first()->id,
            'excursion_id' => Excursion::inRandomOrder()->first()->id,
            'provider_excursion_id' => 1,
        ];
    }
}
