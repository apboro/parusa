<?php

namespace Database\Factories\User;

use App\Models\User\User;
use App\Models\User\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array
     * @throws \Exception
     */
    public function definition(): array
    {
        $gender = ['male', 'female'][random_int(0, 1)];

        return [
            'lastname' => $this->faker->firstName($gender),
            'firstname' => $this->faker->middleName($gender),
            'patronymic' => $this->faker->lastName($gender),
            'gender' => $gender,
        ];
    }
}
