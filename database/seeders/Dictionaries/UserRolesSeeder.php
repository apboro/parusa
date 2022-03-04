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
        ]
    ];
}
