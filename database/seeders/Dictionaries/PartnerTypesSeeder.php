<?php

namespace Database\Seeders\Dictionaries;

use App\Models\Dictionaries\PartnerType;
use App\Models\Dictionaries\PromoCodeType;
use App\Models\Dictionaries\Provider;
use Database\Seeders\GenericSeeder;

class PartnerTypesSeeder extends GenericSeeder
{
    protected array $data = [
        PartnerType::class => [
            PartnerType::hotel => ['name' => 'Гостиница'],
            PartnerType::tour_firm => ['name' => 'Турфирма'],
            PartnerType::horeka => ['name' => 'Хорека'],
            PartnerType::taxi => ['name' => 'Такси'],
            PartnerType::promoter => ['name' => 'Промоутер'],
        ]
    ];
}
