<?php

namespace Database\Seeders\Dictionaries;

use App\Models\Dictionaries\AccountTransactionStatus;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Dictionaries\PiersStatus;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Dictionaries\ShipStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Dictionaries\UserStatus;
use Database\Seeders\GenericSeeder;

class StatusesSeeder extends GenericSeeder
{
    protected array $data = [
        UserStatus::class => [
            UserStatus::active => ['name' => 'Действующий'],
            UserStatus::blocked => ['name' => 'Не действующий'],
        ],
        PartnerStatus::class => [
            PartnerStatus::active => ['name' => 'Действующий'],
            PartnerStatus::blocked => ['name' => 'Не действующий'],
        ],
        PositionStatus::class => [
            PositionStatus::active => ['name' => 'Действующий'],
            PositionStatus::blocked => ['name' => 'Не действующий'],
        ],

        ExcursionStatus::class => [
            ExcursionStatus::active => ['name' => 'Действующий'],
            ExcursionStatus::blocked => ['name' => 'Не действующий'],
        ],
        PiersStatus::class => [
            PiersStatus::active => ['name' => 'Действующий'],
            PiersStatus::blocked => ['name' => 'Не действующий'],
        ],
        ShipStatus::class => [
            ShipStatus::active => ['name' => 'Действующий'],
            ShipStatus::blocked => ['name' => 'Не действующий'],
        ],
        TripStatus::class => [
            TripStatus::active => ['name' => 'Действующий'],
            TripStatus::blocked => ['name' => 'Не действующий'],
        ],

        AccountTransactionStatus::class => [
            AccountTransactionStatus::accepted => ['name' => 'Принято'],
        ]
    ];
}
