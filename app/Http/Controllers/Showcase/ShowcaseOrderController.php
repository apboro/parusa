<?php

namespace App\Http\Controllers\Showcase;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Http\Middleware\ExternalProtect;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Order\Order;
use App\Models\Partner\Partner;
use App\Models\PromoCode\PromoCode;
use App\Models\Sails\Trip;
use App\Models\Tickets\Ticket;
use App\Models\Tickets\TicketRate;
use App\NevaTravel\NevaOrder;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use JsonException;

class ShowcaseOrderController extends ApiEditController
{
    /**
     * Create order for showcase application.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws JsonException
     */
    public function order(Request $request): JsonResponse
    {
        $originalKey = $request->header(ExternalProtect::HEADER_NAME);

        try {
            $originalKey = $originalKey ? json_decode(Crypt::decrypt($originalKey), true, 512, JSON_THROW_ON_ERROR) : null;
        } catch (Exception $exception) {
            return response()->json(['message' => 'Ошибка сессии.'], 400);
        }

        /** @var ?Partner $partner */
        $partner = $originalKey['partner_id'] ? Partner::query()->where('id', $originalKey['partner_id'])->first() : null;
        $partnerId = $partner->id ?? null;
        $isPartnerSite = $originalKey['is_partner'];
        $media = $originalKey['media'] ?? null;

        /** @var Trip $trip */
        $trip = Trip::query()
            ->where('id', $request->input('trip'))
            ->where('start_at', '>', Carbon::now())
            ->whereIn('status_id', [TripStatus::regular])
            ->whereIn('sale_status_id', [TripSaleStatus::selling])
            ->whereHas('excursion.ratesLists', function (Builder $query) use ($isPartnerSite) {
                $query
                    ->whereRaw('DATE(tickets_rates_list.start_at) <= DATE(trips.start_at)')
                    ->whereRaw('DATE(tickets_rates_list.end_at) >= DATE(trips.end_at)')
                    ->whereHas('rates', function (Builder $query) use ($isPartnerSite) {
                        $query->where('grade_id', '!=', TicketGrade::guide);
                        if ($isPartnerSite) {
                            $query->where('site_price', '>', 0);
                        } else {
                            $query->where('base_price', '>', 0);
                        }
                    });
            })
            ->first();

        if ($trip === null) {
            return APIResponse::error('Нет продажи билетов на этот рейс.');
        }


        $flat = $request->input('data');
        $data = Arr::undot($flat);
        $count = count($data['rate'] ?? []);

        if ($count === 0) {
            return APIResponse::error('Нельзя оформить заказ без билетов.');
        }

        $grades = array_keys($data['rate']);
        $count = 0;

        $rules = ['name' => 'required', 'email' => 'required|email|bail', 'phone' => 'required'];
        $titles = ['name' => 'Имя', 'email' => 'Email', 'phone' => 'Телефон'];

        foreach ($grades as $grade) {
            $count += $data['rate'][$grade]['quantity'];
            $rules["rate.$grade.quantity"] = 'nullable|integer|min:0|bail';
            $titles["rate.$grade.quantity"] = 'Количество';
        }

        if ($errors = $this->validate($data, $rules, $titles)) {
            return APIResponse::validationError($errors);
        }

        if ($count === 0) {
            $errors = [];
            foreach ($grades as $grade) {
                $errors["rate.$grade.quantity"] = ['Не выбрано количество'];
            }
            return APIResponse::validationError($errors);
        }

        $rates = $trip->excursion->rateForDate($trip->start_at);
        if ($rates === null) {
            return APIResponse::error('Нет продажи билетов на этот рейс.');
        }

        $tickets = [];

        foreach ($data['rate'] as $gradeId => $grade) {
            if ($grade['quantity'] > 0) {
                // get rate
                /** @var TicketRate $rate */
                $rate = $rates->rates->where('grade_id', $gradeId)->first();
                if ($rate === null) {
                    return APIResponse::error('Нет продажи билетов на этот рейс.');
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


        if ($media === 'qr') {
            $orderType = OrderType::qr_code;
        } else if ($isPartnerSite) {
            $orderType = OrderType::partner_site;
        } else {
            $orderType = OrderType::site;
        }

        try {
            // create order
            $order = Order::make(
                $orderType,
                $tickets,
                OrderStatus::showcase_creating,
                $partnerId,
                null,
                null,
                null,
                $data['email'],
                $data['name'],
                $data['phone'],
                $isPartnerSite === true // strict price checking only for partner site
            );

            if (!empty($flat['promocode'])) {
                $promoCode = PromoCode::query()->where('code', $flat['promocode'])->first();

                if (isset($promoCode)) {
                    $order->promocode()->sync([$promoCode->id]);
                }
            }
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        if (!(new NevaOrder($order))->make()) {
            return APIResponse::error('Невозможно оформить заказ на этот рейс.');
        }

        $orderSecret = json_encode([
            'id' => $order->id,
            'ts' => Carbon::now(),
            'ip' => $request->ip(),
            'ref' => $request->input('ref'),
        ], JSON_THROW_ON_ERROR);

        // clear media and partner cookie after successful order.
        $originalKey = [
            'ip' => $request->ip(),
        ];

        $secret = Crypt::encrypt($orderSecret);

        return response()->json([
            'payload' => [
                'order_id' => $order->id,
                'payment_page' => env('SHOWCASE_PAYMENT_PAGE') . '?order=' . $secret,
                'order_secret' => $secret,
            ],
        ], 200, [ExternalProtect::HEADER_NAME => Crypt::encrypt(json_encode($originalKey, JSON_THROW_ON_ERROR))]);
    }

    /**
     * Cancel order for showcase application.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws JsonException
     */
    public function cancel(Request $request): JsonResponse
    {
        $secret = $request->input('secret');

        try {
            $orderSecret = json_decode(Crypt::decrypt($secret), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            return response()->json([
                'message' => 'Неверные данные заказа.',
                'error' => $exception->getMessage(),
            ], 400);
        }

        /** @var Order|null $order */
        $order = Order::query()->with('status')->where('id', $orderSecret['id'])->first();

        if ($order === null) {
            return APIResponse::error('Заказ не найден.');
        }

        $now = Carbon::now();
        $expires = Carbon::parse($orderSecret['ts'])->setTimezone($now->timezone)->addMinutes(env('SHOWCASE_ORDER_LIFETIME'));

        if ($expires < $now) {
            return APIResponse::success('Время, отведенное на оплату заказа истекло, заказ расформирован.');
        }

        try {
            DB::transaction(static function () use ($order) {
                $order->setStatus(OrderStatus::showcase_canceled);
                $order->tickets->map(function (Ticket $ticket) {
                    $ticket->setStatus(TicketStatus::showcase_canceled);
                });
            });
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success('Заказ расформирован.');
    }
}
