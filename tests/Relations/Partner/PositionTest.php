<?php

namespace Tests\Relations\Partner;

use App\Exceptions\Partner\WrongPartnerStatusException;
use App\Exceptions\Partner\WrongPositionStatusException;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Partner\Partner;
use App\Models\Partner\PartnerUserPosition;
use App\Models\User\User;
use Exception;
use Illuminate\Database\QueryException;
use Tests\Relations\StatusTestTrait;
use Tests\TestCase;

class PositionTest extends TestCase
{
    use StatusTestTrait;

    /**
     * Local user factory.
     *
     * @return PartnerUserPosition
     */
    protected function makePosition(): PartnerUserPosition
    {
        /** @var PartnerUserPosition $partner */
        $partner = PartnerUserPosition::factory()->create();

        return $partner;
    }

    public function testUserStatus(): void
    {
        $this->runStatusTests(
            PartnerUserPosition::class,
            PositionStatus::class,
            WrongPositionStatusException::class,
            PositionStatus::default,
            [$this, 'makePosition']
        );

    }

    public function testRelationsAndLocks(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Partner $partner */
        $partner = Partner::factory()->create();

        /** @var PartnerUserPosition $position */
        $position = PartnerUserPosition::factory()->create(['user_id' => $user->id, 'partner_id' => $partner->id]);

        // check user and position matching
        $this->assertEquals($user->id, $position->user->id);
        $this->assertEquals($partner->id, $position->partner->id);

        // check user locked by position
        $deleted = null;
        try {
            $user->delete();
            $deleted = true;
        } catch (QueryException $e) {
            $deleted = false;
        }
        $this->assertEquals(false, $deleted, 'User deletion must be blocked by PartnerUserPosition');

        // check user locked by position
        $deleted = null;
        try {
            $partner->delete();
            $deleted = true;
        } catch (QueryException $e) {
            $deleted = false;
        }
        $this->assertEquals(false, $deleted, 'Partner deletion must be blocked by PartnerUserPosition');
    }
}
