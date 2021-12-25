<?php

namespace Database\Seeders\Dictionaries;

use App\Models\Dictionaries\AccountTransactionStatus;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Dictionaries\PiersStatus;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Dictionaries\ShipStatus;
use App\Models\Dictionaries\TripDiscountStatus;
use App\Models\Dictionaries\TripSaleStatus;
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
        PositionAccessStatus::class => [
            PositionAccessStatus::active => ['name' => 'Открыт', 'order' => 1],
            PositionAccessStatus::blocked => ['name' => 'Закрыт', 'order' => 2],
        ],

        ExcursionStatus::class => [
            ExcursionStatus::active => ['name' => 'Действующая'],
            ExcursionStatus::blocked => ['name' => 'Не действующая'],
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
            TripStatus::regular => ['name' => 'По расписанию'],
            TripStatus::processing => ['name' => 'Выполняется'],
            TripStatus::finished => ['name' => 'Завершен'],
            TripStatus::cancelled => ['name' => 'Отменен'],
        ],
        TripSaleStatus::class => [
            TripSaleStatus::selling => ['name' => 'Идет продажа'],
            TripSaleStatus::closed_manually => ['name' => 'Продажа закрыта (вручную)'],
            TripSaleStatus::closed_automatically => ['name' => 'Продажа закрыта (автоматически)'],
        ],
        TripDiscountStatus::class => [
            TripDiscountStatus::enabled => ['name' => 'Разрешены'],
            TripDiscountStatus::disabled => ['name' => 'Запрещены'],
        ],

        AccountTransactionStatus::class => [
            AccountTransactionStatus::accepted => ['name' => 'Принято'],
        ]
    ];
}
