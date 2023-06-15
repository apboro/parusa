<?php

namespace App\Http\Controllers\API\PromoCodes;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\PromoCodeStatus;
use App\Models\PromoCode\PromoCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class PromoCodeListController extends ApiController
{
    protected array $defaultFilters = [
        'status_id' => PromoCodeStatus::active,
    ];

    protected array $rememberFilters = [
        'status_id',
    ];

    protected string $rememberKey = CookieKeys::promo_code_list;

    /**
     * Get promo code list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        $query = PromoCode::query()
            ->orderBy('name');

        // apply filters
        if (!empty($filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey)) && !empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        // current page automatically resolved from request via `page` parameter
        $promoCodes = $query->paginate($request->perPage(10, $this->rememberKey));

        /** @var LengthAwarePaginator $promoCodes */
        $promoCodes->transform(function (PromoCode $promoCode) {
            return [
                'active' => $promoCode->hasStatus(PromoCodeStatus::active),
                'id' => $promoCode->id,
                'name' => $promoCode->name,
                'code' => $promoCode->code,
                'purchases' => $promoCode->orders()->whereIn('status_id', OrderStatus::partner_commission_pay_statuses)->count(),
                'status' => $promoCode->status->name,
                'amount' => $promoCode->amount,
                'excursions' => $promoCode->excursions->pluck('name')->toArray(),
            ];
        });

        return APIResponse::list(
            $promoCodes,
            ['Название', 'Промокод', 'Сумма',  'Экскурсии', 'Покупки', 'Статус'],
            $filters,
            $this->defaultFilters,
            []
        )->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }
}
