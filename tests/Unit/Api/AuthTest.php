<?php

namespace Tests\Unit\Api;

use App\Models\Excursions\Excursion;
use App\Models\Partner\Partner;
use Tests\TestCase;

class AuthTest extends TestCase
{

    private Partner $partner;

    public function setUp(): void
    {
        parent::setUp();
        Excursion::factory()->count(5)->create();
        $this->partner = Partner::firstOrCreate([
            'name' => 'test_api_partner'],
            [
                'type_id' => 1001,
                'status_id' => 1,
            ]);
    }

    public function test_error_when_no_token_in_request()
    {
        $response = $this->get('/api/v1/excursions');
        $response->assertStatus(401);
    }

    public function test_get_excursions_success()
    {
        $response = $this->withToken($this->partner->createToken('test_token')
            ->plainTextToken)->get('/api/v1/excursions');

        $response->assertStatus(200);
    }
}
