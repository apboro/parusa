<?php

namespace Database\Seeders;

use App\Models\Partner\Partner;
use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create(['login' => 'admin', 'password' => Hash::make('admin')]);

        Partner::factory(100)->create();
    }
}
