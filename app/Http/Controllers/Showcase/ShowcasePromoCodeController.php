<?php

namespace App\Http\Controllers\Showcase;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Http\Middleware\ExternalProtect;
use App\Models\Dictionaries\PromoCodeStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Partner\Partner;
use App\Models\PromoCode\PromoCode;
use App\Models\Sails\Trip;
use App\Models\Tickets\Ticket;
use App\Models\Tickets\TicketRate;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;

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
        $originalKey = $request->header(ExternalProtect::HEADER_NAME);

        try {
            $originalKey = $originalKey ? json_decode(Crypt::decrypt($originalKey), true, 512, JSON_THROW_ON_ERROR) : null;
        } catch (Exception $exception) {
            return response()->json(['message' => 'Ошибка сессии.'], 400);
        }

        /** @var int|null $partnerID */
//        $partner = $originalKey['partner_id'] ? Partner::query()->where('id', $originalKey['partner_id'])->first() : null;
        $isPartnerSite = $originalKey['is_partner'];
        $promocode = $request->input('promocode');
        $tickets = $request->input('tickets');

        $calc = \App\Helpers\Promocode::calc($promocode, $tickets, $isPartnerSite);

        return APIResponse::response([
            'full_price' => $calc['full_price'],
            'discount_price' => $calc['discount_price'],
            'discounted' => $calc['discounted'],
            'status' => $calc['status'],
            'message' => $calc['message'],
        ]);
    }
}
