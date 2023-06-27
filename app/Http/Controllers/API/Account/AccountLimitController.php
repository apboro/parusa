<?php

namespace App\Http\Controllers\API\Account;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Dictionaries\HitSource;
use App\Models\Hit\Hit;
use App\Models\Partner\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountLimitController extends ApiEditController
{
    protected array $rules = [
        'limit' => 'required|numeric',
    ];

    protected array $titles = [
        'limit' => 'Допустимый остаток по счёту',
    ];

    /**
     * Set tickets for guides quantity.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function setAccountLimit(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $data = $this->getData($request);
        $data['limit'] = (float)$data['limit'];

        if ($errors = $this->validate($data, $this->rules, $this->titles)) {
            return APIResponse::validationError($errors);
        }

        $id = $request->input('id');

        if ($id === null || null === ($partner = Partner::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Партнёр не найден');
        }

        /** @var Partner $partner */

        $partner->account->limit = $data['limit'];
        $partner->account->save();

        return APIResponse::success('Данные обновлены', [
            'id' => $partner->id,
            'limit' => $partner->account->limit,
        ]);
    }
}
