<?php

namespace App\Http\Controllers\Showcase;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\PromoCode\PromoCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowcasePromoCodeController extends ApiEditController
{
    /**
     * Initial promocode for showcase application.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function init(Request $request): JsonResponse
    {
        $code = $request->get('promocode');

        /** @var PromoCode $promoCode */
        if ($code === null ||
            null === ($promoCode = PromoCode::query()->where('code', $code)->first())) {
            return APIResponse::notFound('Введенный вами промокод не действителен');
        }

        return response()->json([
            'discount' => $promoCode->amount
        ]);
    }
}
