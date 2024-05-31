<?php

namespace Tests\NevaTravel;

use App\Models\Dictionaries\Role;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Excursions\Excursion;
use App\Models\Integration\AdditionalDataExcursion;
use App\Models\Order\Order;
use App\Models\POS\Terminal;
use App\Models\Positions\Position;
use App\Models\Positions\PositionOrderingTicket;
use App\Models\Sails\Trip;
use App\Models\Tickets\TicketRate;
use App\Models\Tickets\TicketsRatesList;
use App\Services\NevaTravel\ImportPiers;
use App\Services\NevaTravel\ImportPrograms;
use App\Services\NevaTravel\ImportProgramsPrices;
use App\Services\NevaTravel\ImportShips;
use App\Services\NevaTravel\ImportTrips;
use Str;
use Tests\TestCase;

class NevaTravelIntegrationTest extends TestCase
{
    private PositionOrderingTicket $positionOrderingTicket;
    private mixed $price;
    public function setUp(): void
    {
        parent::setUp();
        $position = Position::factory()->create(['is_staff' => 1, 'partner_id' => null]);
        $terminal = Terminal::factory()->create();
        $ticketGrade = TicketGrade::firstOrCreate(['id'=>50,'provider_id' => 10]);
        $excursion = Excursion::whereHas('additionalData', fn ($q) => $q->where('provider_excursion_id', '3bd55a24-0f3e-11ed-9697-0242c0a8a005'))->first();
        if (!$excursion) {
            $excursion = Excursion::create(['name' => 'neva_travel', 'provider_id' => 10]);
            AdditionalDataExcursion::create([
                'provider_id' => 10,
                'excursion_id' => $excursion->id,
                'provider_excursion_id' => '3bd55a24-0f3e-11ed-9697-0242c0a8a005']);
        }
        (new ImportShips())->run();
        (new ImportPiers())->run();
        (new ImportPrograms())->run();
        (new ImportProgramsPrices())->run();
        (new ImportTrips(now()->addDays(5)))->run();
        $trip = Trip::where('start_at', '>', now())->where('provider_id', 10)->first();
        $this->positionOrderingTicket = PositionOrderingTicket::create([
            'position_id' => $position->id,
            'trip_id' => $trip->id,
            'grade_id' => $ticketGrade->id,
            'terminal_id' => $terminal->id,
            'quantity' => 1
        ]);
        $this->price = $trip->getRate()->rates()->firstWhere('grade_id', $ticketGrade->id)->base_price;
        $terminal->staff()->save($position);
    }

    public function test_make_neva_travel_order_from_terminal()
    {
        $customerEmail = Str::random(5).'@neva.travel';
        $this->disableCookieEncryption();

        $cartOrderId = $this->positionOrderingTicket->id;
        $response = $this->actingAs($this->positionOrderingTicket->position->user)
            ->withCookies([
                'current_user_role' => Role::admin,
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

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Заказ отправлен в оплату.',
        ]);
        $order = Order::where('email', $customerEmail)->first();
        $orderAdditionalData = $order->additionalData;
        $this->assertNotNull($order);
        $this->assertNotNull($orderAdditionalData);
        $this->assertNotNull($orderAdditionalData->provider_order_uuid);

    }
}
