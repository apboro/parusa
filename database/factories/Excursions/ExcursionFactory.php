<?php

namespace Database\Factories\Excursions;

use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Excursions\Excursion;
use App\Models\Tickets\TicketsRatesList;
use Database\Factories\Tickets\TicketsRatesListFactory;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExcursionFactory extends Factory
{
    protected $model = Excursion::class;

     public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'status_id' => ExcursionStatus::default,
        ];
    }

}
