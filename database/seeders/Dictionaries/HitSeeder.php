<?php

namespace Database\Seeders\Dictionaries;

use App\Models\Dictionaries\HitSource;
use Database\Seeders\GenericSeeder;

class HitSeeder extends GenericSeeder
{
    protected array $data = [
        HitSource::class => [
            HitSource::admin => ['name' => 'Кабинет администратора'],
            HitSource::terminal => ['name' => 'Кабинет кассира'],
            HitSource::partner => ['name' => 'Кабинет партнёра'],
            HitSource::showcase => ['name' => 'Витрина'],
            HitSource::checkout => ['name' => 'Страница оформления заказа'],
            HitSource::qrlink => ['name' => 'Переход по qr-коду'],
            HitSource::referal => ['name' => 'Переход по реферальной ссылке'],
        ]
    ];
}
