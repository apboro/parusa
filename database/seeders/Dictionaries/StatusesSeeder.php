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
            UserStatus::active => ['name' => 'Активен'],
            UserStatus::blocked => ['name' => 'Заблокирован'],
        ],
        PartnerStatus::class => [
            PartnerStatus::active => ['name' => 'Активен'],
            PartnerStatus::blocked => ['name' => 'Заблокирован'],
        ],
        PositionStatus::class => [
            PositionStatus::active => ['name' => 'Активен'],
            PositionStatus::blocked => ['name' => 'Заблокирован'],
        ],
        AccountTransactionStatus::class => [
            AccountTransactionStatus::accepted => ['name' => 'Принято'],
        ]
    ];
}
