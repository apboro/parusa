<?php

namespace Tests\NevaTravelTests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiGetRequestTest extends TestCase
{
    public function test_make_get_request_to_api_url()
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'X-API-KEY' => 'ntk_NjY5NmRkZGMtYTNkNC0xMWVkLThjYzAtMDI0MmFjMTcwMDA4',
        ])->get('https://testapi.neva.travel/api/partner/get_api_status');
        $response->assertStatus(200);

    }

}
