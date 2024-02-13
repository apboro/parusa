<?php

namespace Database\Factories;

use App\Models\News\News;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
        ];
    }
}
