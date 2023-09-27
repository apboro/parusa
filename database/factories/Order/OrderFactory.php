<?php

namespace Database\Factories\Order;

use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Order\Order;
use App\Models\Partner\Partner;
use App\Models\POS\Terminal;
use App\Models\Positions\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {

        return [
            'status_id' => OrderStatus::inRandomOrder()->first()->id,
            'type_id' => OrderType::inRandomOrder()->first()->id,
            'partner_id' => Partner::factory()->create()->id,
            'position_id' => Position::factory()->create()->id,
            'terminal_id' => Terminal::factory()->create()->id,
            'terminal_position_id' => Position::inRandomOrder()->first()->id ?? null,
            'external_id' => $this->faker->uuid,
            'payment_unconfirmed' => $this->faker->boolean,
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'phone' => $this->faker->phoneNumber,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];

    }
}
