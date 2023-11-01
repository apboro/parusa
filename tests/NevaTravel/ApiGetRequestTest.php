<?php

namespace Tests\NevaTravel;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class ApiGetRequestTest extends TestCase
{
    public function test_make_get_request_to_api_url()
    {
        $response = Http::withHeaders([
            'X-API-KEY' => 'ntk_NjY5NmRkZGMtYTNkNC0xMWVkLThjYzAtMDI0MmFjMTcwMDA4',
        ])->timeout(3)->get('https://testapi.neva.travel/api/partner/get_api_status');

        assertEquals(200, $response->status());
    }

}
