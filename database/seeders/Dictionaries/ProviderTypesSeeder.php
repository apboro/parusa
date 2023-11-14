<?php

namespace Database\Seeders\Dictionaries;

use App\Models\Dictionaries\PromoCodeType;
use App\Models\Dictionaries\Provider;
use Database\Seeders\GenericSeeder;

class ProviderTypesSeeder extends GenericSeeder
{
    protected array $data = [
        Provider::class => [
            Provider::scarlet_sails => ['name' => 'Алые Паруса', 'service' => 'ships'],
            Provider::neva_travel => ['name' => 'Neva Travel', 'service' => 'ships'],
            Provider::city_tour => ['name' => 'City Tour', 'service' => 'buses'],
            Provider::astra_marine => ['name' => 'Astra Marine', 'service' => 'ships'],
        ]
    ];
}
