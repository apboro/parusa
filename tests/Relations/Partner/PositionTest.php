<?php

namespace Tests\Relations\Partner;

use App\Exceptions\Positions\WrongPositionStatusException;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Partner\Partner;
use App\Models\Partner\PartnerUserPosition;
use App\Models\Positions\Position;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Tests\Relations\StatusTestTrait;
use Tests\TestCase;

class PositionTest extends TestCase
{
    use StatusTestTrait;

    /**
     * Local user factory.
     *
     * @return Collection|Model
     */
    protected function makePosition(): Collection|Model
    {
        return Position::factory()->create();
    }

    public function testUserStatus(): void
    {
        $this->runStatusTests(
            Position::class,
            PositionStatus::class,
            WrongPositionStatusException::class,
            PositionStatus::default,
            [$this, 'makePosition']
        );

    }

    public function testRelationsAndLocks(): void
    {
        $position = Position::factory()->create();

        // check user locked by position
        $deleted = null;
        try {
            $position->user->delete();
            $deleted = true;
        } catch (QueryException $e) {
            $deleted = false;
        }
        $this->assertEquals(true, $deleted, 'User deletion must not be blocked by Position');

        // check user locked by position
        $deleted = null;
        try {
            $position->partner->delete();
            $deleted = true;
        } catch (QueryException $e) {
            $deleted = false;
        }
        $this->assertEquals(true, $deleted, 'Partner deletion must not be blocked by Position');
    }
}
