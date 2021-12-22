<?php

namespace Database\Seeders;

use App\Models\Partner\Partner;
use App\Models\Positions\Position;
use App\Models\Positions\PositionInfo;
use App\Models\Positions\StaffPositionInfo;
use App\Models\Sails\Excursion;
use App\Models\Sails\Pier;
use App\Models\Sails\Ship;
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
        // Create admin user
        /** @var User $admin */
        $admin = User::factory()->create(['login' => 'admin', 'password' => Hash::make('admin')]);
        $admin->staffPosition()->create(['title' => 'Адмнинстратор', 'is_staff' => true]);
        UserProfile::factory()->create(['user_id' => $admin->id]);

        // Create staff users
        User::factory(30)
            ->afterCreating(function (User $user) {
                UserProfile::factory()->create(['user_id' => $user->id]);
                /** @var Position $position */
                $position = Position::factory()->create(['user_id' => $user->id, 'is_staff' => true]);
                StaffPositionInfo::factory()->create(['position_id' => $position->id]);
            })
            ->create();

        // Create partners
        Partner::factory(50)->create();

        // Create users
        User::factory(200)->afterCreating(function (User $user) {
            UserProfile::factory()->create(['user_id' => $user->id]);
        })->create();

        $partners = Partner::query()->pluck('id')->toArray();
        $partnersCount = count($partners);

        // Attach some users to organizations
        $users = User::query()->doesntHave('staffPosition')->get();
        $users->map(function (User $user) use ($partners, $partnersCount) {
            if (random_int(0, 100) > 10) {
                $pc = random_int(1, 3);
                for ($i = 1; $i <= $pc; $i++) {
                    $pid = random_int(1, $partnersCount);
                    /** @var Position $position */
                    $position = Position::factory()->create(['user_id' => $user->id, 'partner_id' => $pid]);
                    PositionInfo::factory()->create(['position_id' => $position->id]);
                }
            }
        });

        Ship::factory(30)->create();
        Pier::factory(30)->create();
        Excursion::factory(30)->create();
    }
}
