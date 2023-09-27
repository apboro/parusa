<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketGrade;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketGradeFactory extends Factory
{
    protected $model = TicketGrade::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'provider_id' => Provider::inRandomOrder()->first()->id,
        ];
    }
}
