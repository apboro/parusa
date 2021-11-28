<?php

namespace Database\Seeders\Dictionaries;

use App\Models\Dictionaries\AccountTransactionStatus;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Dictionaries\UserStatus;
use Database\Seeders\GenericSeeder;

class StatusesSeeder extends GenericSeeder
{
    protected array $data = [
        UserStatus::class => [
            UserStatus::blocked => ['name' => 'Заблокирован'],
            UserStatus::active => ['name' => 'Активен'],
        ],
        PartnerStatus::class => [
            PartnerStatus::blocked => ['name' => 'Заблокирован'],
            PartnerStatus::active => ['name' => 'Активен'],
        ],
        PositionStatus::class => [
            PositionStatus::blocked => ['name' => 'Заблокирован'],
            PositionStatus::active => ['name' => 'Активен'],
        ],
        AccountTransactionStatus::class => [
            AccountTransactionStatus::accepted => ['name' => 'Принято'],
        ]
    ];
}
