<?php

namespace Tests\Relations\User;

use App\Exceptions\User\WrongUserStatusException;
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
}
