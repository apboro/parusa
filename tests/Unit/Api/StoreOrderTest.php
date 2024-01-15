<?php

namespace Tests\Unit\Api;

use App\Models\Excursions\Excursion;
use App\Models\Partner\Partner;
use App\Models\Sails\Trip;
use Tests\TestCase;

class StoreOrderTest extends TestCase
{

    private string $token;
    private Trip $trip;

    public function setUp(): void
    {
        parent::setUp();

        Excursion::factory()->count(5)->create();
        $this->trip = Trip::factory()->create();
        $partner = Partner::firstOrCreate([
            'name' => 'test_api_partner'],
            [
                'type_id' => 1001,
                'status_id' => 1,
            ]);
        $this->token = $partner->createToken('test_token')
            ->plainTextToken;
    }

    public function test_store_order_request()
    {
//        $response = $this->withToken($this->token)->post('/api/v1/order',
//            [
//                'client_name' => 'test_api_client',
//                'client_email' => 'testapiemail@mail.ru',
//                'client_phone' => '78944588745',
//                'trip_id' => $this->trip->id,
//                'tickets' => [
//                    ['grade_id' => 1003], ['grade_id' => 1030]
//                ]
//            ]);
//        $response->assertStatus(404);
    }


}
