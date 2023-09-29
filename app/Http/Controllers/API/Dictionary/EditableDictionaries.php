<?php

namespace App\Http\Controllers\API\Dictionary;

use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\PartnerType;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Ships\Ship;
use App\Models\Tariff;

trait EditableDictionaries
{
    protected array $dictionaries = [
        'ships' => [
            'name' => 'Теплоходы',
            'class' => Ship::class,
            'item_name' => 'теплоход',
            'titles' => [
                'name' => 'Название теплохода',
                'capacity' => 'Вместимость',
                'owner' => 'Владелец',
                'description' => 'Описание',
            ],
            'validation' => [
                'name' => 'required',
                'capacity' => 'required|integer|min:0',
                'owner' => 'required',
            ],
            'fields' => [
                'name' => 'string',
                'capacity' => 'number',
                'owner' => 'string',
                'description' => 'text',
            ],
            // 'hide' => ['description'],
            'auto' => 'description',
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
                'pay_per_hour' => 'Оплата в час, руб.',
                'pay_for_out' => 'Оплата за выход, руб.',
                'commission' => 'Комиссия, %'
            ],
            'validation' => [
                'name' => 'required',
                'pay_per_hour' => 'integer',
                'pay_for_out' => 'integer',
                'commission' => 'integer'
            ],

        ],
    ];
}
