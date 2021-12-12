<?php

namespace Database\Seeders;

use App\Models\Partner\Partner;
use App\Models\Partner\PartnerUserPosition;
use App\Models\Sails\Ship;
use App\Models\Staff\StaffUserPosition;
use App\Models\User\User;
use App\Models\User\UserProfile;
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
        User::factory(200)->create(['is_staff' => 0]);
        User::factory(30)->create(['is_staff' => 1]);

        $partners = Partner::query()->pluck('id')->toArray();
        $partnersCount = count($partners);

        $users = User::query()->where('is_staff', false)->get();
        $users->map(function (User $user) use ($partners, $partnersCount) {
            if (random_int(0, 100) > 10) {
                $pc = random_int(1, 3);
                for ($i = 1; $i <= $pc; $i++) {
                    $pid = random_int(1, $partnersCount);
                    PartnerUserPosition::factory()->create(['user_id' => $user->id, 'partner_id' => $pid]);
                }

            }
            UserProfile::factory()->create(['user_id' => $user->id]);
        });

        $staff = User::query()->where('is_staff', true)->get();
        $staff->map(function (User $user) {
            StaffUserPosition::factory()->create(['user_id' => $user->id]);
            UserProfile::factory()->create(['user_id' => $user->id]);
        });

        Ship::factory(30)->create();
    }
}
