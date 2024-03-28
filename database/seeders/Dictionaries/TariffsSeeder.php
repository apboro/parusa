<?php

namespace Database\Seeders\Dictionaries;

use App\Models\Dictionaries\PromoCodeType;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\Tariff;
use App\Models\NewsRecipients;
use Database\Seeders\GenericSeeder;

class TariffsSeeder extends GenericSeeder
{
    protected array $data = [
        Tariff::class => [
            Tariff::standard => ['name' => 'Стандарт с 9:30 до 11:00', 'invisible' => false, 'commission' => 25],
        ]
    ];
}
