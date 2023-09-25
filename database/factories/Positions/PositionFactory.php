<?php

namespace Database\Factories\Positions;

use App\Models\Partner\Partner;
use App\Models\Positions\Position;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionFactory extends Factory
{
    protected $model = Position::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $userId = null;
        $partnerId = null;

        if (is_null($userId)) {
            $user = User::factory()->create();
            $userId = $user->id;
        }
        if (is_null($partnerId)) {
            $partner = Partner::factory()->create();
            $partnerId = $partner->id;
        }

        return [
            'user_id' => $userId,
            'partner_id' => $partnerId,
            'title' => $this->faker->jobTitle,
        ];
    }
}
