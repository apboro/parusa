<?php

namespace Tests\Relations\User;

use App\Exceptions\User\WrongUserStatusException;
use App\Models\Dictionaries\Role;
use App\Models\Dictionaries\UserStatus;
use App\Models\User\User;
use Tests\Relations\StatusTestTrait;
use Tests\TestCase;

class UserTest extends TestCase
{
    use StatusTestTrait;

    /**
     * Local user factory.
     *
     * @return User
     */
    protected function makeUser(): User
    {
        /** @var User $user */
        $user = User::factory()->create();

        return $user;
    }

    public function testUserStatus(): void
    {
        $this->runStatusTests(
            User::class,
            UserStatus::class,
            WrongUserStatusException::class,
            UserStatus::default,
            [$this, 'makeUser']
        );
    }

    public function testUserRoles(): void
    {
        $user = $this->makeUser();

        // default role not set
        $this->assertEmpty($user->roles, 'Default UserRole for User must be empty');

        // default relation is empty
        $this->assertFalse($user->hasRole(Role::admin, true), 'Default UserRole for User must be empty');

        // attach role
        $user->roles()->attach(Role::admin);
        $this->assertTrue($user->hasRole(Role::admin, true), 'Error attaching UserRole to User');

        // detach role
        $user->roles()->detach(Role::admin);
        $this->assertFalse($user->hasRole(Role::admin, true), 'Error attaching UserRole to User');

        // attach role
        $user->roles()->sync([Role::admin]);
        $this->assertTrue($user->hasRole(Role::admin, true), 'Error attaching UserRole to User');
    }
}
