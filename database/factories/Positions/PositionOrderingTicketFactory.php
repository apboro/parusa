<?php

namespace Database\Factories\Positions;

use App\Models\Dictionaries\TicketGrade;
use App\Models\POS\Terminal;
use App\Models\Positions\Position;
use App\Models\Positions\PositionOrderingTicket;
use App\Models\Sails\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionOrderingTicketFactory extends Factory
{

    protected $model = PositionOrderingTicket::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'position_id' => Position::factory()->create()->id,
            'terminal_id' => Terminal::factory()->create()->id,
            'trip_id' => Trip::factory()->create()->id,
            'grade_id' => TicketGrade::factory()->create()->id,
            'quantity' => 1
        ];
    }
}
