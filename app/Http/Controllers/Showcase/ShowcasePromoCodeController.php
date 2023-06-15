<?php

namespace App\Http\Controllers\Showcase;

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

        /** @var ?Partner $partner */
        $isPartnerSite = $originalKey['is_partner'];

        $tripID = $request->get('trip');
        $flat = $request->input('data');
        $data = Arr::undot($flat);

        $tickets_count = array_column($data['rate'], 'quantity');
        $tickets_count = array_sum($tickets_count);

        $full_price = 0;
        $discount_price = 0;
        $discounted = 0;

        if ($tickets_count < 1) {
            return response()->json([
                'full_price' => $full_price,
                'discount_price' => $discount_price,
                'discounted' => $discounted,
                'status' => false,
                'message' => 'В заказе нет билетов.'
            ]);
        }

        $trip = Trip::find($tripID);
        $rates = $trip->excursion->rateForDate($trip->start_at);
        if ($rates === null) {
            return response()->json([
                'full_price' => $full_price,
                'discount_price' => $discount_price,
                'discounted' => $discounted,
                'status' => false,
                'message' => 'Нет продажи билетов на этот рейс.'
            ]);
        }

        $tickets = [];
        foreach ($data['rate'] as $gradeId => $grade) {
            if ($grade['quantity'] > 0) {
                // get rate
                /** @var TicketRate $rate */
                $rate = $rates->rates->where('grade_id', $gradeId)->first();
                if ($rate === null) {
                    return response()->json([
                        'full_price' => $full_price,
                        'discount_price' => $discount_price,
                        'discounted' => $discounted,
                        'status' => false,
                        'message' => 'Нет продажи билетов на этот рейс.'
                    ]);
                }
                for ($i = 1; $i <= $grade['quantity']; $i++) {
                    $ticket = new Ticket([
                        'trip_id' => $trip->id,
                        'grade_id' => $gradeId,
                        'status_id' => TicketStatus::showcase_creating,
                        'base_price' => $isPartnerSite ? $rate->base_price : $rate->site_price,
                        'neva_travel_ticket' => $trip->source === 'NevaTravelApi',
                    ]);
                    $tickets[] = $ticket;
                }
            }
        }

        $prices = array_column($tickets, 'base_price');
        $full_price = array_sum($prices);

        $code = mb_strtoupper($request->input('promocode'));
        if ($code === null) {
            return response()->json([
                'full_price' => $full_price,
                'discount_price' => $full_price,
                'discounted' => 0,
                'status' => true,
                'message' => ''
            ]);
        }

        /** @var PromoCode $promoCode */
        if (null === ($promoCode = PromoCode::query()->where('code', $code)->where('status_id', PromoCodeStatus::active)->first())) {
            return response()->json([
                'full_price' => $full_price,
                'discount_price' => $full_price,
                'discounted' => 0,
                'status' => false,
                'message' => 'Введенный вами промокод не действителен.'
            ]);
        }

        if (empty($promoCode->excursions->where('id', $trip->excursion_id)->first())) {
            return response()->json([
                'full_price' => $full_price,
                'discount_price' => $full_price,
                'discounted' => 0,
                'status' => false,
                'message' => 'Действие промокода не распространяется на выбранную экскурсию.'
            ]);
        }

        return response()->json([
            'full_price' => $full_price,
            'discount_price' => $full_price - $promoCode->amount,
            'discounted' => $promoCode->amount,
            'status' => true,
            'message' => ''
        ]);
    }
}
