<?php

namespace Database\Seeders\Initial;

use App\Models\Dictionaries\TicketGrade;
use Database\Seeders\GenericSeeder;

class TicketGradesSeeder extends GenericSeeder
{
    /**
     * @var array|string[][][]
     *
     * Defaults:
     * 'enabled' => true
     * 'lock' => false
     */
    protected array $data = [
        TicketGrade::class => [
            1 => ['name' => 'Взрослый', 'order' => 0],
            2 => ['name' => 'Пенсионный', 'order' => 1],
            3 => ['name' => 'Студенческий', 'order' => 2],
            4 => ['name' => 'Школьный', 'order' => 3],
            5 => ['name' => 'Детский (до 1 года)', 'order' => 4],
            6 => ['name' => 'Гид', 'order' => 5],
        ],
    ];
}
