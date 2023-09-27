<?php

namespace Tests\Unit;

use App\Http\Middleware\EncryptCookies;
use App\Models\Dictionaries\Role;
use App\Models\POS\Terminal;
use App\Models\Positions\Position;
use App\Models\Positions\PositionOrderingTicket;
use App\Services\CityTourBus\CityTourRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class CityTourIntegrationTest extends TestCase
{

    private CityTourRepository $cityTourRepository;
    private PositionOrderingTicket $positionOrderingTicket;

    private Terminal $terminal;

    public function setUp(): void
    {
        parent::setUp();
        $this->cityTourRepository = new CityTourRepository();
        $position = Position::factory()->create(['is_staff' => 1, 'partner_id' => null]);
        $this->positionOrderingTicket = PositionOrderingTicket::factory()->create(['position_id' => $position->id]);
        $this->terminal = Terminal::factory()->create();
        $this->terminal->staff()->save($position);
    }

    public function test_city_tour_api_available()
    {
        $this->assertEquals(200, $this->cityTourRepository->getExcursions()['status']);
    }

    public function test_make_city_tour_order_from_terminal()
    {
        $this->disableCookieEncryption();

        $cartOrderId = $this->positionOrderingTicket->id;
        $response = $this->actingAs($this->positionOrderingTicket->position->user)
            ->withCookies([
                'current_user_role' => $this->positionOrderingTicket->position->roles()->save(Role::find(2))->id,
                'current_user_position' => $this->positionOrderingTicket->position_id,
                'current_user_terminal' => $this->terminal->id,
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

    }
}
