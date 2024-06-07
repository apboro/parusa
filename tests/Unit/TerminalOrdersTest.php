<?php

namespace Tests\Unit;

use App\Models\Dictionaries\Role;
use App\Models\POS\Terminal;
use Illuminate\Support\Str;
use Tests\TestCase;

class TerminalOrdersTest extends TestCase
{
    public Terminal $terminal;
    public function setUp(): void
    {
        parent::setUp();
        $this->terminal = Terminal::factory()->create();
        $this->terminal->staff()->save($this->kassir->staffPosition);

    }
    public function testMakeOrder()
    {
        $this->disableCookieEncryption();
        $customerEmail = Str::random(5).'@promoter.ru';

        $response = $this->actingAs($this->kassir)
            ->withCookies([
            'current_user_role' => Role::terminal,
            'current_user_position' => $this->kassir->staffPosition->id,
            'current_user_terminal' => $this->terminal->id,
        ])->post('/api/order/terminal/make',[
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
    }
}
