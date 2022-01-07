<?php

namespace Database\Seeders\Initial;

use App\Models\Dictionaries\PartnerType;
use Database\Seeders\GenericSeeder;

class PartnerTypesSeeder extends GenericSeeder
{
    /**
     * @var array|string[][][]
     *
     * Defaults:
     * 'enabled' => true
     * 'lock' => false
     */
    protected array $data = [
        PartnerType::class => [
            1000 => ['name' => 'Гостиница'],
            1001 => ['name' => 'Ресторан'],
            1002 => ['name' => 'Тур-оператор'],
            1003 => ['name' => 'Промоутер'],
            1004 => ['name' => 'Отель'],
        ]
    ];
}
