<?php

namespace Database\Seeders\Dictionaries;

use App\Models\Dictionaries\ExcursionType;
use App\Models\Dictionaries\PromoCodeType;
use App\Models\Dictionaries\Provider;
use Database\Seeders\GenericSeeder;

class ExcursionTypesSeeder extends GenericSeeder
{
    protected array $data = [
        ExcursionType::class => [
            ExcursionType::water => ['name' => 'Водные экскурсии'],
            ExcursionType::earth => ['name' => 'Автобусные экскурсии '],
            ExcursionType::combined => ['name' => 'Комбинированные экскурсии'],
            ExcursionType::legs => ['name' => 'Пешие прогулки'],
            ExcursionType::standUp => ['name' => 'Stand Up'],
        ]
    ];
}
