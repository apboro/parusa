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
            TicketGrade::guide => ['name' => 'Гид', 'order' => 5, 'locked' => true],

            1000 => ['name' => 'Взрослый', 'order' => 0],
            1001 => ['name' => 'Пенсионный', 'order' => 1],
            1002 => ['name' => 'Студенческий', 'order' => 2],
            1003 => ['name' => 'Школьный', 'order' => 3],
            1004 => ['name' => 'Детский (до 1 года)', 'order' => 4],
        ],
    ];
}
