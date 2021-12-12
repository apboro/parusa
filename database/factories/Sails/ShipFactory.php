<?php

namespace Database\Factories\Sails;

use App\Models\Dictionaries\ShipStatus;
use App\Models\Sails\Ship;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShipFactory extends Factory
{
    protected $model = Ship::class;

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
            'status_id' => ShipStatus::default,
            'type_id' => null,
            'owner' => $this->faker->company,
            'decks' => ['Однопалубный', 'Двухпалубный', 'Трёхпалубный'][random_int(0, 2)],
            'capacity' => [50, 100, 150, 200][random_int(0, 3)],
        ];
    }
}
