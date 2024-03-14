<?php

namespace Database\Seeders\Dictionaries;

use App\Models\Dictionaries\PromoCodeType;
use App\Models\Dictionaries\Provider;
use Database\Seeders\GenericSeeder;

class ProviderTypesSeeder extends GenericSeeder
{
    protected array $data = [
        Provider::class => [
            Provider::scarlet_sails => ['name' => 'Алые Паруса', 'has_integration' => false, 'locked' => true, 'service' => 'ships'],
            Provider::neva_travel => ['name' => 'Neva Travel', 'has_integration' => true, 'locked' => true, 'service' => 'ships'],
            Provider::city_tour => ['name' => 'City Tour', 'has_integration' => true, 'locked' => true, 'service' => 'buses'],
            Provider::astra_marine => ['name' => 'Astra Marine', 'has_integration' => true, 'locked' => true, 'service' => 'ships'],
        ]
    ];
}
