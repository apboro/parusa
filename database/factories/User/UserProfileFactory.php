<?php

namespace Database\Factories\User;

use App\Models\User\UserProfile;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array
     * @throws Exception
     */
    public function definition(): array
    {
        $gender = ['male', 'female'][random_int(0, 1)];

        return [
            'lastname' => $this->faker->lastName($gender),
            'firstname' => $this->faker->firstName($gender),
            'patronymic' => $this->faker->middleName($gender),
            'gender' => $gender,
            'birthdate' => $this->faker->date('Y-m-d', '-20 years'),
            'default_position_title' => $this->faker->jobTitle,
            'email' => $this->faker->email,
            'work_phone' => $this->faker->phoneNumber,
            'work_phone_additional' => random_int(0, 1) === 1 ? $this->faker->numberBetween(100, 999) : null,
            'mobile_phone' => $this->faker->phoneNumber,
            'vkontakte' => $this->faker->url,
            'facebook' => $this->faker->url,
            'telegram' => '@' . $this->faker->word,
            'skype' => $this->faker->word,
            'whatsapp' => $this->faker->phoneNumber,
            'notes' => $this->faker->text,
        ];
    }
}
