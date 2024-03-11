<?php

namespace App\Http\Controllers\API\Dictionary;

use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\Inventory;
use App\Models\Dictionaries\PartnerType;
use App\Models\Dictionaries\Tariff;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Ships\Seats\SeatCategory;

trait EditableDictionaries
{
    protected array $dictionaries = [
        'seat_categories' => [
            'name' => 'Категории мест',
            'class' => SeatCategory::class,
            'item_name' => 'категорию места',
            'validation' => [
                'name' => 'required',
                'table_seats_quantity' => 'integer|nullable'
            ],
            'titles' => [
                'name' => 'Название категории',
                'table_seat' => 'Столик',
                'table_seats_quantity' => 'Количество мест за столом'
            ],
            'fields' => [
                'name' => 'string',
                'table_seat' => 'bool',
                'table_seats_quantity' => 'integer'
            ],
        ],
        'ticket_grades' => [
            'name' => 'Типы билетов',
            'class' => TicketGrade::class,
            'item_name' => 'тип билета',
            'validation' => ['name' => 'required'],
            'titles' => ['name' => 'Тип билета', 'preferential' => 'Льготный'],
            'fields' => ['name' => 'string', 'preferential' => 'bool'],
        ],
        'excursion_programs' => [
            'name' => 'Типы программ',
            'class' => ExcursionProgram::class,
            'item_name' => 'тип программы',
            'validation' => ['name' => 'required'],
            'titles' => ['name' => 'Тип программы'],
            'fields' => ['name' => 'string'],
        ],
        'partner_types' => [
            'name' => 'Типы партнёров',
            'class' => PartnerType::class,
            'item_name' => 'тип партнёра',
            'validation' => ['name' => 'required'],
            'titles' => ['name' => 'Тип партнёра'],
            'fields' => ['name' => 'string'],
        ],
        'tariffs' => [
            'name' => 'Тарифы промоутеров',
            'class' => Tariff::class,
            'item_name' => 'тариф',
            'titles' => [
                'name' => 'Тариф',
                'pay_per_hour' => 'Оплата в час, руб.',
                'pay_for_out' => 'Оплата за выход, руб.',
                'commission' => 'Комиссия, %'
            ],
            'fields' => [
                'name' => 'string',
                'pay_per_hour' => 'integer',
                'pay_for_out' => 'integer',
                'commission' => 'integer'
            ],
            'validation' => [
                'name' => 'required',
                'pay_per_hour' => 'integer',
                'pay_for_out' => 'integer',
                'commission' => 'integer|max:100'
            ],
        ],
        'inventory' => [
            'name' => 'Инвентарь промоутеров',
            'class' => Inventory::class,
            'item_name' => 'предмет',
            'titles' => [
                'name' => 'Предмет',
            ],
            'fields' => [
                'name' => 'string',
            ],
            'validation' => [
                'name' => 'required',
            ],
        ],
    ];
}
