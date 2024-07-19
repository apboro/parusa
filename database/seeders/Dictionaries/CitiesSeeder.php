<?php

namespace Database\Seeders\Dictionaries;

use App\Models\City;
use Database\Seeders\GenericSeeder;

class CitiesSeeder extends GenericSeeder
{
    protected array $data = [
        City::class => [
            City::spb => ['name' => 'Санкт-Петербург'],
            City::kazan => ['name' => 'Казань'],
        ]
    ];
}
