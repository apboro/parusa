<?php

namespace Database\Seeders\Dictionaries;

use App\Models\Dictionaries\PromoCodeType;
use App\Models\Dictionaries\Provider;
use Database\Seeders\GenericSeeder;

class TypesSeeder extends GenericSeeder
{
    protected array $data = [
        PromoCodeType::class => [
            PromoCodeType::fixed => ['name' => 'Фиксированный'],
        ],
        Provider::class => [
            Provider::scarlet_sails => ['name' => 'Алые Паруса', 'service' => 'ships'],
            Provider::neva_travel => ['name' => 'Neva Travel', 'service' => 'ships'],
            Provider::city_tour => ['name' => 'City Tours Spb', 'service' => 'buses'],
        ]
    ];
}
