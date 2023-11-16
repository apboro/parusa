<?php

namespace Tests;

use App\Models\Dictionaries\Role;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
//    use RefreshDatabase;

    protected User $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('login', 'admin')->first() ?? User::factory()->create(['login' => 'admin', 'password' => Hash::make('admin')])->first();
        if ($this->admin->wasRecentlyCreated) {
            $this->admin->staffPosition()->create(['title' => 'Админинстратор', 'is_staff' => 1]);
            $this->admin->profile()->create(['lastname' => 'Администратор', 'firstname' => 'Администратор', 'gender' => 'male']);
            $this->admin->staffPosition->roles()->attach(Role::admin);
        }
    }

}
