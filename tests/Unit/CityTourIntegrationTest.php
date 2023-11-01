<?php

namespace Tests\Unit;

use App\Models\Dictionaries\Role;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Excursions\Excursion;
use App\Models\Integration\AdditionalDataExcursion;
use App\Models\POS\Terminal;
use App\Models\Positions\Position;
use App\Models\Positions\PositionOrderingTicket;
use App\Models\Sails\Trip;
use App\Models\Tickets\TicketRate;
use App\Models\Tickets\TicketsRatesList;
use App\Services\CityTourBus\CityTourRepository;
use Tests\TestCase;

class CityTourIntegrationTest extends TestCase
{


    private PositionOrderingTicket $positionOrderingTicket;

    private Terminal $terminal;

    public function setUp(): void
    {
        parent::setUp();
        $position = Position::factory()->create(['is_staff' => 1, 'partner_id' => null]);
        $this->terminal = Terminal::factory()->create();
        $ticketGrade = TicketGrade::factory()->create(['id'=>178,'provider_id' => 20]);
        $excursion = Excursion::create(['name'=>'city_tour', 'provider_id' => 20]);
        AdditionalDataExcursion::create([
            'provider_id' => 20,
            'excursion_id' => $excursion->id,
            'provider_excursion_id' => 1]);
        $ticketsRateList = TicketsRatesList::factory()->create(['excursion_id' => $excursion->id]);
        TicketRate::factory()->create(['rate_id' => $ticketsRateList->id, 'grade_id' => $ticketGrade->id]);
        $trip = Trip::factory()->create(['excursion_id' => $excursion->id, 'provider_id' => 20]);

        $this->positionOrderingTicket = PositionOrderingTicket::create([
            'position_id' => $position->id,
            'trip_id' => $trip->id,
            'grade_id' => $ticketGrade->id,
            'terminal_id' => $this->terminal->id,
            'quantity' => 1
        ]);
        $this->terminal->staff()->save($position);
    }

    public function test_make_city_tour_order_from_terminal()
    {
        $this->disableCookieEncryption();

        $cartOrderId = $this->positionOrderingTicket->id;
        $response = $this->actingAs($this->positionOrderingTicket->position->user)
            ->withCookies([
                'current_user_role' => $this->positionOrderingTicket->position->roles()->save(Role::find(2))->id,
                'current_user_position' => $this->positionOrderingTicket->position_id,
                'current_user_terminal' => $this->positionOrderingTicket->terminal_id,
            ])->post('/api/order/terminal/make',
                [
                    'mode' => 'order',
                    "data" => [
                        "tickets.$cartOrderId.price" => 1000,
                        "tickets.$cartOrderId.quantity" => 1,
                        "partner_id" => null,
                        "without_partner" => true,
                        "name" => null,
                        "email" => null,
                        "phone" => "+7 (666) 666-66-66"
                    ]
                ]);
        $response->dump();
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Заказ отправлен в оплату.',
        ]);
    }
}
