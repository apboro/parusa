<?php

namespace App\Http\Controllers\API\Dictionary;

use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\PartnerType;
use App\Models\Sails\Ship;

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
            'hide' => ['description'],
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
    ];
}
