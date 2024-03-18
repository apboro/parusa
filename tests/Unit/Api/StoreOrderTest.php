<?php

namespace Tests\Unit\Api;

use App\Models\Dictionaries\TicketGrade;
use App\Models\Excursions\Excursion;
use App\Models\Partner\Partner;
use App\Models\Sails\Trip;
use App\Models\Tickets\TicketRate;
use App\Models\Tickets\TicketsRatesList;
use Tests\TestCase;

class StoreOrderTest extends TestCase
{

    private string $token;
    private Trip $trip;
    private int $gradeId;

    public function setUp(): void
    {
        parent::setUp();

        $excursion = Excursion::factory()->create();
        $ticketRateList = TicketsRatesList::create([
            'excursion_id' => $excursion->id,
            'start_at' => now()->addMinutes(10),
            'end_at' => now()->addDays(10)
        ]);
        $this->gradeId = TicketGrade::factory()->create()->id;
        TicketRate::factory()->create(['rate_id' => $ticketRateList->id, 'grade_id' => $this->gradeId]);
        $this->trip = Trip::factory()->create(['excursion_id' => $excursion->id]);
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
        $phone = rand(10000,5000000);
        $response = $this->withToken($this->token)->post('/api/v1/order',
            [
                'client_name' => 'test_api_client',
                'client_email' => 'testapiEmail@mail.ru',
                'client_phone' => $phone,
                'trip_id' => $this->trip->id,
                'tickets' => [
                    ['grade_id' => $this->gradeId]
                ]
            ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', ['phone' => $phone]);
    }
}
