<?php

namespace Database\Seeders\Dictionaries;

use App\Models\Dictionaries\UserRole;
use Database\Seeders\GenericSeeder;

class UserRolesSeeder extends GenericSeeder
{
    protected array $data = [
        UserRole::class => [
            UserRole::admin => ['name' => 'Администратор'],
        ]
    ];
}
