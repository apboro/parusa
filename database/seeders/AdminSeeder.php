<?php

namespace Database\Seeders;

use App\Models\Dictionaries\Role;
use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Create admin user
        /** @var User $admin */
        if (User::query()->where('id', 1)->count() === 0) {
            $admin = User::factory()->create(['login' => 'admin', 'password' => Hash::make('admin')]);
            $admin->staffPosition()->create(['title' => 'Адмнинстратор', 'is_staff' => true]);
            $admin->profile()->create(['lastname' => 'Администратор', 'firstname' => 'Администратор', 'gender' => 'male']);
            $admin->staffPosition->roles()->attach(Role::admin);
        }
        if (User::query()->where('id', 2)->count() === 0) {
            $admin = User::factory()->create(['login' => 'nechaev@mail.ru', 'password' => Hash::make('000000')]);
            $admin->staffPosition()->create(['title' => 'Директор', 'is_staff' => true]);
            $admin->profile()->create(['lastname' => 'Нечаев', 'firstname' => 'Дмитрий', 'gender' => 'male']);
            $admin->staffPosition->roles()->attach(Role::admin);
        }
    }
}
