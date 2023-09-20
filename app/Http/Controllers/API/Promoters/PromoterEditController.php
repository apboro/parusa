<?php

namespace App\Http\Controllers\API\Promoters;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Common\File;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\PartnerType;
use App\Models\Hit\Hit;
use App\Models\Partner\Partner;
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
    ];

    protected array $titles = [
        'last_name' => 'Фамилия',
        'first_name' => 'Имя',
        'patronymic' => 'Отчество',
        'email' => 'Почта',
        'phone' => 'Телефон',
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
        $promoterUser = $partner->positions()->first()?->user->profile;

        // send response
        return APIResponse::form(
            [
                'last_name' => $promoterUser->lastname ?? null,
                'first_name' => $promoterUser->firstname ?? null,
                'patronymic' => $promoterUser->patronymic ?? null,
                'email' => $promoterUser->email ?? null,
                'phone' => $promoterUser->mobile_phone ?? null,
                'status_id' => $partner->status_id ?? null,
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
        if (!$promoterUser){
            $promoterUser = new User();
        }
        $promoterUser->save();

        $promoterUserProfile = $promoterUser->profile;

        $promoterUserProfile->lastname = $data['last_name'];
        $promoterUserProfile->firstname = $data['first_name'];
        $promoterUserProfile->patronymic = $data['patronymic'];
        $promoterUserProfile->email = $data['email'];
        $promoterUserProfile->mobile_phone = $data['phone'];
        $promoterUserProfile->save();

        $partner->name = $promoterUserProfile->fullName;
        $partner->type_id = PartnerType::promoter;
        $partner->status_id = $data['status_id'];
        $partner->save();

        $profile = $partner->profile;

        $profile->tickets_for_guides = 0;
        $profile->save();

        $position = $partner->positions()->first();

        return APIResponse::success('Данные промоутера обновлены', [
            'id' => $partner->id,
            'title' => $partner->name,
        ]);
    }

}
