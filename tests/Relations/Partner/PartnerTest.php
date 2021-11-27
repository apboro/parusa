<?php

namespace Tests\Relations\Partner;

use App\Exceptions\Partner\WrongPartnerStatusException;
use App\Exceptions\Partner\WrongPartnerTypeException;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Dictionaries\PartnerType;
use App\Models\Partner\Partner;
use Tests\Relations\StatusTestTrait;
use Tests\Relations\TypeTestTrait;
use Tests\TestCase;

class PartnerTest extends TestCase
{
    use StatusTestTrait, TypeTestTrait;

    /**
     * Local user factory.
     *
     * @param array|null $arguments
     *
     * @return Partner
     */
    protected function makePartner(?array $arguments = null): Partner
    {
        /** @var Partner $partner */
        $partner = $arguments === null ? Partner::factory()->create() : Partner::factory()->create($arguments);

        return $partner;
    }

    public function testPartnerStatus(): void
    {
        $this->runStatusTests(
            Partner::class,
            PartnerStatus::class,
            WrongPartnerStatusException::class,
            PartnerStatus::default,
            [$this, 'makePartner']
        );

    }

    public function testPartnerTypes(): void
    {
        $this->runTypeTests(
            Partner::class,
            PartnerType::class,
            WrongPartnerTypeException::class,
            1000,
            [$this, 'makePartner']
        );

    }
}
