<?php

namespace App\Http\Controllers\API\PromoCodes;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Dictionaries\PromoCodeStatus;
use App\Models\Excursions\Excursion;
use App\Models\PromoCode\PromoCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromoCodeEditController extends ApiEditController
{
    protected array $rules = [
        'name' => 'required',
        'status_id' => 'required',
        'code' => 'required',
        'amount' => 'required',
        'excursions' => 'required',
    ];

    protected array $titles = [
        'name' => 'Название',
        'status_id' => 'Статус',
        'code' => 'Код',
        'amount' => 'Сумма',
        'excursions' => 'Выберите экскурсии',
    ];

    /**
     * Get edit data for promo code.
     * id === 0 is for new
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function get(Request $request): JsonResponse
    {
        /** @var PromoCode|null $promoCode */
        $promoCode = $this->firstOrNew(PromoCode::class, $request, ['status']);

        if ($promoCode === null) {
            return APIResponse::notFound('Промокод не найден');
        }

        $excursions = Excursion::query()->where('status_id', ExcursionStatus::active)->get();

        // send response
        return APIResponse::form(
            [
                'name' => $promoCode->name,
                'code' => $promoCode->code,
                'amount' => $promoCode->amount,
                'status_id' => $promoCode->status_id,
                'excursions' => $promoCode->excursions,
            ],
            $this->rules,
            $this->titles,
            [
                'name' => $promoCode->exists ? $promoCode->name : 'Добавление промокода',
                'excursions' => $excursions->map(function (Excursion $excursion) {
                    return [
                        'id' => $excursion->id,
                        'name' => $excursion->name,
                    ];
                }),
            ]
        );
    }

    /**
     * Update promo code data.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        /** @var PromoCode $promoCode */
        $promoCode = $this->firstOrNew(PromoCode::class, $request);

        if ($promoCode === null) {
            return APIResponse::notFound('Промокод не найден');
        }

        $data = $this->getData($request);
        $data['code'] = isset($data['code']) ? mb_strtoupper($data['code']) : null;

        $this->rules['name'] = [
            'required',
            Rule::unique('promo_codes')->ignore($promoCode->id),
        ];
        $this->rules['code'] = [
            'required',
            Rule::unique('promo_codes')->where('status_id', PromoCodeStatus::active)->ignore($promoCode->id),
        ];

        if ($errors = $this->validate($data, $this->rules, $this->titles)) {
            return APIResponse::validationError($errors);
        }

        $promoCode->setAttribute('name', $data['name']);
        $promoCode->setAttribute('code', $data['code']);
        $promoCode->setAttribute('amount', $data['amount']);
        $promoCode->setStatus($data['status_id'], false);
        $promoCode->save();

        $promoCode->excursions()->sync($data['excursions']);

        return APIResponse::success(
            $promoCode->wasRecentlyCreated ? 'Промокод добавлен' : 'Данные промокода обновлены',
            [
                'id' => $promoCode->id,
                'name' => $promoCode->name,
            ]
        );
    }

    /**
     * Update status promo code
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function status(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if (empty($id) || empty($promoCode = PromoCode::query()->where('id', $id)->first())) {
            return APIResponse::error('Ошибка! Статус не изменен.');
        }
        /** @var PromoCode $promoCode */

        if (
            $promoCode->status_id === PromoCodeStatus::blocked
            && PromoCode::query()
                ->where('status_id', PromoCodeStatus::active)
                ->where('code', $promoCode->code)
                ->where('id', '!=', $promoCode->id)
                ->count() > 0
        ) {
            return APIResponse::error('Есть активный промокод с таким кодом');
        }

        $promoCode->status_id = $promoCode->status_id === PromoCodeStatus::active ? PromoCodeStatus::blocked : PromoCodeStatus::active;
        $promoCode->save();

        return APIResponse::success($promoCode->status_id === PromoCodeStatus::active ? 'Промокод активирован' : 'Промокод деактивирован');
    }
}
