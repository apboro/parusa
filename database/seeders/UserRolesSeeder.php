<?php

namespace Database\Seeders;

use App\Models\User\UserRole;

class UserRolesSeeder extends GenericSeeder
{
    protected array $data = [
        UserRole::class => [
            [UserRole::admin => ['name' => 'Администратор']],
        ]
    ];
}
