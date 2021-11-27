<?php

namespace Database\Seeders\Dictionaries;

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
        ]
    ];
}
