<?php

namespace Database\Seeders\Dictionaries;

use App\Models\Dictionaries\Role;
use Database\Seeders\GenericSeeder;

class UserRolesSeeder extends GenericSeeder
{
    protected array $data = [
        Role::class => [
            Role::admin => ['name' => 'Администратор'],
            Role::terminal => ['name' => 'Кассир'],
            Role::office_manager => ['name' => 'Менеджер'],
            Role::piers_manager => ['name' => 'Управляющий причала'],
            Role::accountant => ['name' => 'Бухгалтер'],
            Role::controller => ['name' => 'Контролёр'],
        ]
    ];
}
