<?php

namespace Database\Factories\Partner;

use App\Models\Partner\Partner;
use App\Models\Partner\PartnerUserPosition;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerUserPositionFactory extends Factory
{
    protected $model = PartnerUserPosition::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Partner $partner */
        $partner = Partner::factory()->create();

        return [
            'partner_id' => $partner->id,
            'user_id' => $user->id,
            'position_title' => $this->faker->jobTitle,
        ];
    }
}
