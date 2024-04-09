<?php

namespace App\Http\Controllers\API\Promoters;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Common\File;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\PartnerType;
use App\Models\Dictionaries\Tariff;
use App\Models\Hit\Hit;
use App\Models\Partner\Partner;
use App\Models\Positions\Position;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoterEditController extends ApiEditController
{
    protected array $rules = [
        'last_name' => 'required',
        'first_name' => 'required',
        'email' => 'required|email',
        'phone' => 'required',
        'status_id' => 'required',
        'promoter_commission_rate' => 'required|max:100'
    ];

    protected array $titles = [
        'last_name' => 'Фамилия',
        'first_name' => 'Имя',
        'patronymic' => 'Отчество',
        'can_send_sms' => 'Разрешена отправка СМС',
        'email' => 'Почта',
        'phone' => 'Телефон',
        'notes' => 'Заметки',
        'pay_per_hour' => 'Почасовая ставка, руб.',
        'auto_change_tariff' => 'Автоматическая смена тарифа',
        'promoter_commission_rate' => 'Ставка комиссии, %'
    ];

    /**
     * Get edit data for partner.
     * id === 0 is for new
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function get(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        /** @var Partner|null $partner */
        $partner = $this->firstOrNew(Partner::class, $request);

        if ($partner === null) {
            return APIResponse::notFound('Промоутер не найден');
        }
        $promoterUserProfile = $partner->positions()->first()?->user->profile;

        // send response
        return APIResponse::form(
            [
                'last_name' => $promoterUserProfile->lastname ?? null,
                'first_name' => $promoterUserProfile->firstname ?? null,
                'patronymic' => $promoterUserProfile->patronymic ?? null,
                'can_send_sms' => $partner->profile->can_send_sms,
                'email' => $promoterUserProfile->email ?? null,
                'phone' => $promoterUserProfile->mobile_phone ?? null,
                'status_id' => $partner->status_id ?? null,
                'notes' => $promoterUserProfile->notes ?? null,
                'pay_per_hour' => $partner->tariff()->first()?->pay_per_hour ?? '—',
                'auto_change_tariff' => $partner->profile->auto_change_tariff,
                'promoter_commission_rate' => $partner->tariff()->first()?->commission
            ],
            $this->rules,
            $this->titles,
            [
                'title' => $partner->exists ? $partner->name : 'Добавление промоутера',
            ]
        );
    }

    /**
     * Update excursion data.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        /** @var Partner|null $partner */
        $partner = $this->firstOrNew(Partner::class, $request);

        if ($partner === null) {
            return APIResponse::notFound('Промоутер не найден');
        }

        $data = $this->getData($request);

        if ($errors = $this->validate($data, $this->rules, $this->titles)) {
            return APIResponse::validationError($errors);
        }

        $promoterUser = $partner->positions()->first()?->user;
        if (!$promoterUser) {
            $promoterUser = new User();
            $promoterUser->save();
        }

        $promoterUserProfile = $promoterUser->profile;

        $promoterUserProfile->lastname = $data['last_name'];
        $promoterUserProfile->firstname = $data['first_name'];
        $promoterUserProfile->patronymic = $data['patronymic'];
        $promoterUserProfile->email = $data['email'];
        $promoterUserProfile->mobile_phone = $data['phone'];
        $promoterUserProfile->notes = $data['notes'];
        $promoterUserProfile->save();

        $partner->name = $promoterUserProfile->fullName;
        $partner->type_id = PartnerType::promoter;
        $partner->status_id = $data['status_id'];
        $partner->save();

        $promoterUserPosition = Position::firstOrNew(['partner_id' => $partner->id]);
        $promoterUserPosition->user_id = $promoterUser->id;
        $promoterUserPosition->status_id = 1;
        $promoterUserPosition->access_status_id = 1;
        $promoterUserPosition->partner_id = $partner->id;
        $promoterUserPosition->title = "Промоутер";
        $promoterUserPosition->is_staff = 0;
        $promoterUserPosition->save();

        $profile = $partner->profile;

        $profile->can_send_sms = $data['can_send_sms'];
        $profile->auto_change_tariff = $data['auto_change_tariff'] ?? 0;
        $profile->tickets_for_guides = 0;
        $profile->can_reserve_tickets = 0;
        $profile->save();

        $tariff = Tariff::query()
            ->firstOrCreate([
                'invisible' => true,
                'commission' => $data['promoter_commission_rate'],
                'pay_per_hour' => $data['pay_per_hour']
            ]);
        $partner->tariff()->sync($tariff->id);

        return APIResponse::success('Данные промоутера обновлены', [
            'id' => $partner->id,
            'title' => $partner->name,
        ]);
    }

}
