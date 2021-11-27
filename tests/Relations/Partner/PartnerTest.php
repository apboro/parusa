<?php

namespace Tests\Relations\Partner;

use App\Exceptions\Partner\WrongPartnerStatusException;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Partner\Partner;
use Tests\Relations\StatusTestTrait;
use Tests\TestCase;

class PartnerTest extends TestCase
{
    use StatusTestTrait;

    /**
     * Local user factory.
     *
     * @return Partner
     */
    protected function makePartner(): Partner
    {
        /** @var Partner $partner */
        $partner = Partner::factory()->create();

        return $partner;
    }

    public function testUserStatus(): void
    {
        $this->runStatusTests(
            Partner::class,
            PartnerStatus::class,
            WrongPartnerStatusException::class,
            PartnerStatus::default,
            [$this, 'makePartner']
        );

    }
}
