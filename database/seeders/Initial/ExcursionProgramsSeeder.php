<?php

namespace Database\Seeders\Initial;

use App\Models\Dictionaries\ExcursionProgram;
use Database\Seeders\GenericSeeder;

class ExcursionProgramsSeeder extends GenericSeeder
{
    /**
     * @var array|string[][][]
     *
     * Defaults:
     * 'enabled' => true
     * 'lock' => false
     */
    protected array $data = [
        ExcursionProgram::class => [
            1 => ['name' => 'Дневная'],
            2 => ['name' => 'Ночная'],
            3 => ['name' => 'Развлекательная'],
            4 => ['name' => 'Экскурсионная'],
            5 => ['name' => 'Обзорная'],
        ]
    ];
}
