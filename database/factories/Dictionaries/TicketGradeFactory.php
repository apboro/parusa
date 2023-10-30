<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketGrade;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketGradeFactory extends Factory
{
    protected $model = TicketGrade::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'provider_id' => Provider::inRandomOrder()->first()->id,
        ];
    }
}
