<?php

namespace Database\Seeders\Dictionaries;

use App\Models\Dictionaries\PromoCodeType;
use Database\Seeders\GenericSeeder;

class TypesSeeder extends GenericSeeder
{
    protected array $data = [
        PromoCodeType::class => [
            PromoCodeType::fixed => ['name' => 'Фиксированный'],
        ],
    ];
}
