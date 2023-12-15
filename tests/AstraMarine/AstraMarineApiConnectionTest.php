<?php

namespace Tests\AstraMarine;

use App\Models\Dictionaries\Role;
use App\Models\Excursions\Excursion;
use App\Models\Integration\AdditionalDataExcursion;
use App\Models\Order\Order;
use App\Models\POS\Terminal;
use App\Models\Positions\Position;
use App\Models\Positions\PositionOrderingTicket;
use App\Models\Sails\Trip;
use App\Services\AstraMarine\ImportTrips;
use Illuminate\Support\Str;
use Tests\TestCase;

class AstraMarineApiConnectionTest extends TestCase
{

    private int $price;

    public function setUp(): void
    {
        parent::setUp();
        $position = Position::factory()->create(['is_staff' => 1, 'partner_id' => null]);
        $terminal = Terminal::factory()->create();
        $excursion = Excursion::create(['name'=>'astra_marine', 'provider_id' => 30]);
        AdditionalDataExcursion::create([
            'provider_id' => 30,
            'excursion_id' => $excursion->id,
            'provider_excursion_id' => '000000003']);
        (new ImportTrips())->run();
        $trip = Trip::where('start_at', '>', now())->where('provider_id', 30)->first();
        $ship = $trip->ship;
        $seat = $ship->seats[rand(10,60)];
        $grade = $ship->seat_categories_ticket_grades()->where('seat_category_id', $seat->category->id)
            ->whereHas('grade', function ($grade){
                $grade->where('has_menu', true);
            })->first()->grade;
        $menu = $grade->menus[0];
        $this->positionOrderingTicket = PositionOrderingTicket::create([
            'position_id' => $position->id,
            'trip_id' => $trip->id,
            'grade_id' => $grade->id,
            'terminal_id' => $terminal->id,
            'quantity' => 1,
            'seat_id' => $seat->id,
            'menu_id' => $menu->id,
        ]);
        $this->price = $trip->getRate()->rates->where('grade_id', $grade->id)->first()->base_price;
        $terminal->staff()->save($position);
    }

    public function test_astra_marine_api()
    {
        $customerEmail = Str::random(5).'@astra.marine';
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
                        "tickets.$cartOrderId.price" => $this->price,
                        "tickets.$cartOrderId.quantity" => 1,
                        "partner_id" => null,
                        "without_partner" => true,
                        "name" => null,
                        "email" => $customerEmail,
                        "phone" => "+7 (666) 666-66-66"
                    ]
                ]);
        $response->dump();
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Заказ отправлен в оплату.',
        ]);
        $order = Order::where('email', $customerEmail)->with('tickets')->first();
        $this->assertNotNull($order);

        foreach ($order->tickets as $ticket){
            $this->assertNotNull($ticket->additionalData);
            $this->assertNotNull($ticket->additionalData->provider_qr_code);
        }
    }
}
