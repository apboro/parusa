<?php

namespace Tests\Feature\Promoters;

use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\PartnerType;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Excursions\Excursion;
use App\Models\Integration\AdditionalDataExcursion;
use App\Models\Order\Order;
use App\Models\Partner\Partner;
use App\Models\Positions\Position;
use App\Models\Positions\PositionOrderingTicket;
use App\Models\Sails\Trip;
use App\Models\Tickets\TicketRate;
use App\Models\Tickets\TicketsRatesList;
use Str;
use Tests\TestCase;

class CreatePromoterOrderTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        $promoter = Partner::firstOrCreate(['id' => 84, 'name'=>'promoter_order_test', 'type_id' => PartnerType::promoter, 'status_id' => 1]);
        $position = Position::factory()->create(['is_staff' => 0, 'partner_id' => 84]);
        $ticketGrade = TicketGrade::firstOrCreate(['id'=>2,'provider_id' => 5]);
        $excursion = Excursion::create(['name'=>'test_ap', 'provider_id' => 5]);
        AdditionalDataExcursion::create([
            'provider_id' => 5,
            'excursion_id' => $excursion->id,
            'provider_excursion_id' => 1]);
        $ticketsRateList = TicketsRatesList::factory()->create(['excursion_id' => $excursion->id]);
        TicketRate::factory()->create(['rate_id' => $ticketsRateList->id, 'grade_id' => $ticketGrade->id]);
        $trip = Trip::factory()->create(['excursion_id' => $excursion->id, 'provider_id' => 5]);

        $this->positionOrderingTicket = PositionOrderingTicket::create([
            'position_id' => $position->id,
            'trip_id' => $trip->id,
            'grade_id' => $ticketGrade->id,
            'terminal_id' => null,
            'quantity' => 1
        ]);
    }
    public function testCreateOrderByPromoter()
    {
        $customerEmail = Str::random(5).'@mail.ru';
        $this->disableCookieEncryption();

        $cartOrderId = $this->positionOrderingTicket->id;
        $response = $this->actingAs($this->positionOrderingTicket->position->user)
            ->withCookies([
                'current_user_position' => $this->positionOrderingTicket->position_id,
            ])->post('/api/order/promoter/make',
                [
                    'mode' => 'order',
                    "data" => [
                        "tickets.$cartOrderId.price" => 1000,
                        "tickets.$cartOrderId.quantity" => 1,
                        "partner_id" => 84,
                        "without_partner" => false,
                        "name" => 'Promoter Order Test',
                        "email" => $customerEmail,
                        "phone" => "+7 (666) 666-66-66"
                    ]
                ]);
        $response->dump();
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Заказ создан, ожидает оплаты.',
        ]);
        $order = Order::where('email', $customerEmail)->first();
        $this->assertNotNull($order);
        $this->assertEquals(OrderStatus::promoter_wait_for_pay, $order->status_id);
    }
}
