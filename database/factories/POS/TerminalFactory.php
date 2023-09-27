<?php

namespace Database\Factories\POS;

use App\Models\Piers\Pier;
use App\Models\POS\Terminal;
use Illuminate\Database\Eloquent\Factories\Factory;

class TerminalFactory extends Factory
{
    protected $model=Terminal::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'status_id' => 1,
            'pier_id' => Pier::factory()->create()->id,
        ];
    }
}
