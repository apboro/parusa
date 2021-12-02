<?php

namespace Database\Factories\Staff;

use App\Models\Staff\StaffUserPosition;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffUserPositionFactory extends Factory
{
    protected $model = StaffUserPosition::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        /** @var User $user */
        $user = User::factory()->create();

        return [
            'user_id' => $user->id,
            'position_title' => $this->faker->jobTitle,
        ];
    }
}
