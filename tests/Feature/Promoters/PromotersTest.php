<?php

namespace Tests\Feature\Promoters;

use App\Models\Partner\Partner;
use App\Models\User\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\Fluent\AssertableJson;
use Str;
use Tests\TestCase;

class PromotersTest extends TestCase
{

    private Partner $promoter;

    public function setUp(): void
    {
        parent::setUp();

        $this->promoter = Partner::factory()->create(['name' => 'Promoter', 'type_id' => 1003]);
        $this->promoter->account()->create();

        $promoterUser = User::factory()->create(['login' => null, 'password' => null]);
        $promoterUser->positions()->create(['title' => 'Промоутер', 'is_staff' => 0, 'partner_id' => $this->promoter->id]);
        $promoterUser->profile()->create([
            'lastname' => 'Promoter',
            'firstname' => 'Test',
            'email' => 'promoter@test.ru',
            'mobile_phone' => '+795001769570',
            'notes' => 'test promoter',
        ]);

    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_promoters_list()
    {
        //в данный момент тест обходит проверку ролей
        //todo внедрить проверку ролей в тестах

        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)
//            ->withCookies([
//                'current_user_role' => Crypt::encryptString(1),
//                'current_user_position' => Crypt::encryptString(2)
//            ])
            ->postJson('/api/promoters');

        $response
            ->assertStatus(200)
            ->assertJson(fn(AssertableJson $json) => $json->hasAll([
                'message', 'filters', 'filters_original', 'titles', 'payload', 'pagination', 'list'])
                ->has('list.0', fn($list) => $list->hasAll('id', 'active', 'name', 'type', 'balance', 'limit', 'open_shift', 'promoter_commission_rate', 'pier_name')
                ));
    }

    public function test_promoters_create()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)
            ->postJson('/api/promoters/update', [
                'id' => 0,
                'data' => [
                    'last_name' => 'Promoter',
                    'first_name' => 'Test',
                    'patronymic' => null,
                    'email' => 'promoter@test.ru',
                    'phone' => '+7 (222) 222-22-22',
                    'status_id' => 1,
                    'notes' => 'Test notes',
                    'can_send_sms' => false,
                    'promoter_commission_rate' => 20,
                    'pay_per_hour'  => 0
                ]
            ]);

        $response->assertJson(function (AssertableJson $json) {
            $json->hasAll(['status', 'message', 'code', 'payload'])
                ->has('payload', function ($json) {
                    $json->hasAll(['id', 'title']);
                });
        });

        $this->assertDatabaseHas('partners', ['name' => 'Promoter Test']);
        $this->assertDatabaseHas('user_profiles', ['email' => 'promoter@test.ru']);
        $this->assertDatabaseHas('positions', ['title' => 'Промоутер']);
    }

    public function test_promoter_make_access()
    {
        $this->withoutMiddleware();

        $promoterUser = User::factory()->create(['login' => null, 'password' => null])->first();
        $login = Str::random(6);

        $response = $this->actingAs($this->admin)->postJson('/api/representatives/access/set', [
            'id' => $promoterUser->id,
            'data' => [
                'login' => $login,
                'password' => '111111',
                'password_confirmation' => '111111',
                'is_send_email' => false,
                'email' => 'promoter23@test.ru',
            ]
        ]);

        $response->assertJson(function (AssertableJson $json) use ($login) {
            $json->hasAll(['status', 'message', 'code', 'payload'])
                ->has('payload', function ($json) use ($login) {
                    $json->where('has_access', true)
                        ->where('login', $login);
                });
        });

        $this->assertTrue(Auth::attempt(['login' => $login, 'password' => '111111']));
    }

    public function test_promoter_view()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)
            ->postJson('/api/promoters/view', ['id' => $this->promoter->id]);

        $response->assertJson(function (AssertableJson $json) {
            $json->hasAll(['message', 'status', 'code',
                'data',
                'data.id',
                'data.representativeId',
                'data.name',
                'data.created_at',
                'data.notes',
                'data.phone',
                'data.email',
                'data.has_access',
                'data.login',
                'data.full_name',
                'payload'
            ]);
        });
    }

    public function test_promoters_delete()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)->postJson('/api/promoters/delete', ['id' => $this->promoter->id]);

        $response->assertJson(function (AssertableJson $json) {
            $json->where('code', 200)
                ->where('message', 'Промоутер удалён')
                ->etc();
        });

    }
}
