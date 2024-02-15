<?php

namespace Database\Seeders\Dictionaries;

use App\Models\Dictionaries\PromoCodeType;
use App\Models\Dictionaries\Provider;
use App\Models\NewsRecipients;
use Database\Seeders\GenericSeeder;

class TypesSeeder extends GenericSeeder
{
    protected array $data = [
        PromoCodeType::class => [
            PromoCodeType::fixed => ['name' => 'Фиксированный'],
        ],
        NewsRecipients::class => [
            NewsRecipients::PARTNERS => ['name' => 'Все партнеры'],
            NewsRecipients::CLIENTS => ['name' => 'Клиенты'],
        ]
    ];
}
