<?php

namespace Database\Seeders\Dictionaries;

use App\Models\Dictionaries\WorkShiftStatus;
use Database\Seeders\GenericSeeder;
use Illuminate\Database\Seeder;

class WorkShiftStatusesSeeder extends GenericSeeder
{

        protected array $data = [
        WorkShiftStatus::class => [
            WorkShiftStatus::active => ['name' => 'Открыта'],
            WorkShiftStatus::closed => ['name' => 'Закрыта'],
        ]
    ];

}
