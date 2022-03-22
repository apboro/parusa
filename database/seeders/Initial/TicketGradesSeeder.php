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
            TicketGrade::adult => ['name' => 'Взрослый', 'order' => 0, 'locked' => true],
            TicketGrade::guide => ['name' => 'Гид', 'order' => 5, 'locked' => true],
            1000 => ['name' => 'Пенсионный', 'order' => 1],
            1001 => ['name' => 'Студенческий', 'order' => 2],
            1002 => ['name' => 'Школьный', 'order' => 3],
            1003 => ['name' => 'Детский (до 1 года)', 'order' => 4],
        ],
    ];
}
