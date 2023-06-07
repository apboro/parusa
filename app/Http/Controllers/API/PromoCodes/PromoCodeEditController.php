<?php

namespace App\Http\Controllers\API\PromoCodes;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Dictionaries\PromoCodeStatus;
use App\Models\PromoCode\PromoCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        /** @var PromoCode|null $excursion */
        $promoCode = $this->firstOrNew(PromoCode::class, $request, ['status']);

        if ($promoCode === null) {
            return APIResponse::notFound('Промокод не найдена');
        }

        // send response
        return APIResponse::form(
            [
                'name' => $promoCode->name,
                'code' => $promoCode->code,
                'amount' => $promoCode->amount,
                'status_id' => $promoCode->status_id,
            ],
            $this->rules,
            $this->titles,
            [
                'name' => $promoCode->exists ? $promoCode->name : 'Добавление промокода',
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
        $data = $this->getData($request);

        if ($errors = $this->validate($data, $this->rules, $this->titles)) {
            return APIResponse::validationError($errors);
        }

        /** @var PromoCode|null $excursion */
        $promoCode = $this->firstOrNew(PromoCode::class, $request);

        if ($promoCode === null) {
            return APIResponse::notFound('Промокод не найден');
        }

        $promoCode->setAttribute('name', $data['name']);
        $promoCode->setAttribute('code', $data['code']);
        $promoCode->setAttribute('amount', $data['amount']);
        $promoCode->setStatus($data['status_id'], false);
        $promoCode->save();

        $promoCode->excursions()->sync($data['excursions']);

        return APIResponse::success(
            $promoCode->wasRecentlyCreated ? 'Экскурсия добавлена' : 'Данные экскурсии обновлены',
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

        if (empty($id) || empty($promoCode = PromoCode::find($id))) {
            return APIResponse::error('Ошибка! Статус не изменен.');
        }

        $promoCode->status_id = $promoCode->status_id == PromoCodeStatus::active ? PromoCodeStatus::blocked : PromoCodeStatus::active;
        $promoCode->save();

        return APIResponse::success('Статус изменен',
            [
                'has_access' => false,
                'login' => null,
            ]
        );
    }
}
