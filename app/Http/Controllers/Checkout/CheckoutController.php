<?php

namespace App\Http\Controllers\Checkout;

use App\Helpers\StatisticQrCodes;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Jobs\ProcessShowcaseConfirmedOrder;
use App\Models\Common\Image;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\PaymentStatus;
use App\Models\Hit\Hit;
use App\Models\Order\Order;
use App\Models\Partner\Partner;
use App\Models\Payments\Payment;
use App\Models\QrCodes\QrCode;
use App\Models\Sails\Trip;
use App\Models\Tickets\Ticket;
use App\SberbankAcquiring\Connection;
use App\SberbankAcquiring\Helpers\Currency;
use App\SberbankAcquiring\HttpClient\CurlClient;
use App\SberbankAcquiring\Options;
use App\SberbankAcquiring\Sber;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use JsonException;

class CheckoutController extends ApiController
{
    protected const COOKIE_NAME = 'ap-checkout-session';

    /**
     * Initial data for showcase application.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws JsonException
     */
    public function handle(Request $request): JsonResponse
    {
        Hit::register(HitSource::checkout);
        // Handle success response
        if ($request->has('status')) {
            return $this->handleResponse($request);
        }

        // Handle common request
        if ($request->has('order')) {
            return $this->handleOrderRequest($request);
        }

        if ($request->hasCookie(self::COOKIE_NAME)) {
            $cookies = $this->getCookies($request);
            if (!empty($cookies['ref'])) {
                $backLink = $cookies['ref'];
            }
        }

        return APIResponse::error('Нет заказов, находящихся в оформлении.', [
            'back_link' => $backLink ?? config('showcase.showcase_ap_page'),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws JsonException
     */
    protected function handleOrderRequest(Request $request): JsonResponse
    {
        $secret = $request->input('order');

        try {
            $container = Crypt::decrypt($secret);
            $container = json_decode($container, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            return APIResponse::error('Заказ не найден.', [$exception->getMessage()]);
        }

        // check "credentials"
        if ($container['ip'] !== $request->ip()) {
            return APIResponse::error('Ошибка сессии.');
        }

        // check order
        $order = $this->getOrder($container['id'] ?? null);
        if ($order === null) {
            return APIResponse::error('Заказ не найден.');
        }

        // check timestamp against order states
        $ts = Carbon::parse($container['ts']);
        $validThrough = $ts->clone()->addMinutes(config('showcase.showcase_order_lifetime'));
        $now = Carbon::now();

        if (in_array($order->status_id, OrderStatus::sberpay_statuses)) {
            if ($validThrough < $now) {
                return APIResponse::error('Время, отведенное на оплату заказа, закончилось. Заказ расформирован.', [
                    'back_link' => $container['ref'] ?? null,
                ]);
            }
        } else {
            return APIResponse::error('Заказ ' . mb_strtolower($order->status->name), [
                'back_link' => $container['ref'] ?? null,
            ]);
        }

        $trips = new Collection();

        $tickets = $order->tickets->map(function (Ticket $ticket) use ($trips) {
            if (!isset($trips[$ticket->trip_id])) {
                $trips[$ticket->trip_id] = $ticket->trip;
            }
            return [
                'id' => $ticket->id,
                'trip_id' => $ticket->trip_id,
                'grade' => $ticket->grade->name,
                'grade_id' => $ticket->grade_id,
                'base_price' => $ticket->base_price,
            ];
        });

        return response()->json([
            'order' => [
                'id' => $order->id,
                'secret' => $secret,
                'back_link' => $container['ref'] ?? null,
                'total' => $order->total(),
                'name' => $order->name,
                'email' => $order->email,
                'phone' => $order->phone,
                'tickets' => $tickets,
                'trips' => $trips->map(function (Trip $trip) {
                    return [
                        'id' => $trip->id,
                        'start_date' => $trip->start_at->format('d.m.Y'),
                        'start_time' => $trip->start_at->format('H:i'),
                        'pier' => $trip->startPier->name,
                        'pier_id' => $trip->start_pier_id,
                        'excursion' => $trip->excursion->name,
                        'excursion_id' => $trip->excursion_id,
                        'duration' => $trip->excursion->info->duration,
                        'images' => $trip->excursion->images->map(function (Image $image) {
                            return $image->url;
                        }),
                    ];
                })->values(),
            ],
            'lifetime' => $validThrough > $now ? $validThrough->diffInSeconds($now) : null,

        ])->withCookie(cookie(self::COOKIE_NAME, json_encode($container, JSON_THROW_ON_ERROR)));
    }

    protected function handleResponse(Request $request): JsonResponse
    {
        $secret = $request->input('order');

        try {
            $container = Crypt::decrypt($secret);
            $container = json_decode($container, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            return APIResponse::error('Заказ не найден.');
        }

        // check "credentials"
        if ($container['ip'] !== $request->ip()) {
            return APIResponse::error('Ошибка сессии.');
        }

        // check order
        $order = $this->getOrder($container['id'] ?? null);
        if ($order === null) {
            return APIResponse::error('Заказ не найден.');
        }
        if ($order->external_id === null) {
            return APIResponse::error('Заказ не был корректно передан в оплату.');
        }

        $client = new \YooKassa\Client();
        $client->setAuth(config('youkassa.shop_id'), config('youkassa.secret_key'));

        $paymentId = $order->external_id;
        try {
            $response = $client->getPaymentInfo($paymentId);
        } catch (Exception $exception) {
            Log::channel('youkassa')->error('Order '. $order->id. 'get status client error: ' .$exception->getMessage());
            return APIResponse::error($exception->getMessage());
        }

        if ($response['status'] !== 'succeeded') {
            Log::channel('youkassa')->info('Order '. $order->id. ' get status error: ' . $response->getStatus());
            return APIResponse::error('Заказ не оплачен. ' . $response->getStatus());
        }

        // set order status
        if (in_array($order->status_id, [
            OrderStatus::showcase_creating,
            OrderStatus::showcase_wait_for_pay,
            OrderStatus::promoter_wait_for_pay,
            OrderStatus::partner_wait_for_pay])) {

            $newOrderStatus = match ($order->type_id) {
                OrderType::promoter_sale => OrderStatus::promoter_confirmed,
                OrderType::partner_sale => OrderStatus::partner_paid_by_link,
                default => OrderStatus::showcase_confirmed,
            };

            if (!in_array($order->status_id, [OrderStatus::partner_wait_for_pay])) {

                $existingCookieHash = $request->cookie('qrCodeHash');
                try {
                    if ($existingCookieHash) {
                        /** @var QrCode|null $qrCode */
                        $qrCode = QrCode::query()->where('hash', $existingCookieHash)->first();
                        if ($qrCode) {
                            $order->partner_id = $qrCode->partner_id;
                            $order->type_id = OrderType::qr_code;
                            StatisticQrCodes::addPayment($existingCookieHash);
                        }
                    }
                } catch (Exception $e) {
                    Log::channel('youkassa')->error('Error with qr statistics: ' . $e->getMessage());
                }

                $referralCookie = $request->cookie('referralLink');
                try {
                    if ($referralCookie) {
                        /**@var Partner|null $partner */
                        $partner = Partner::query()->where('id', $referralCookie)->first();
                        if ($partner) {
                            $order->partner_id = $partner->id;
                            $order->type_id = OrderType::referral_link;
                        }
                    }
                } catch (Exception $e) {
                    Log::channel('youkassa')->error('Error with referral statistics: ' . $e->getMessage());
                }
            }
            Log::channel('youkassa')->info('Order '. $order->id. ' payment confirmed');

            $order->setStatus($newOrderStatus);

            // add payment
            $payment = new Payment();
            $payment->gate = 'youkassa';
            $payment->order_id = $order->id;
            $payment->status_id = PaymentStatus::sale;
            $payment->fiscal = '';
            $payment->total = $response['amount']['value'] ?? null;
            $payment->by_card = $response['amount']['value'] ?? null;
            $payment->by_cash = 0;
            $payment->save();

            // Make job to do in background:
            // make fiscal
            // send tickets
            // pay commission
            // update order status
            ProcessShowcaseConfirmedOrder::dispatch($order->id);
        }

        $orderSecret = json_encode([
            'id' => $order->id,
            'ts' => Carbon::now(),
            'ip' => $request->ip(),
            'ref' => $request->input('ref'),
        ], JSON_THROW_ON_ERROR);

        $secret = Crypt::encrypt($orderSecret);

        // response OK
        $backLink = $container['ref'] ?? config('showcase.showcase_ap_page');
        $query = parse_url($backLink, PHP_URL_QUERY);
        if ($query) {
            $backLink .= '&status=finished&secret=' . $secret;
        } else {
            $backLink .= '?status=finished&secret=' . $secret;
        }

        return APIResponse::success('Заказ оплачен. Высылаем чек и билеты на электронную почту.', [
            'is_ordered' => true,
            'back_link' => $backLink,
        ]);
    }

    /**
     * Get cookies.
     *
     * @param Request $request
     *
     * @return  array|null
     */
    protected function getCookies(Request $request): ?array
    {
        $container = $request->cookie(self::COOKIE_NAME);
        try {
            $container = json_decode($container, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            return null;
        }
        return $container;
    }

    /**
     * @param int|null $id
     *
     * @return  Order|null
     */
    protected function getOrder(?int $id): ?Order
    {
        /** @var Order $order */
        $order = Order::query()
            ->with(
                [
                    'status',
                    'tickets',
                    'tickets.grade',
                    'tickets.trip',
                    'tickets.trip.startPier',
                    'tickets.trip.startPier.info',
                    'tickets.trip.excursion',
                    'tickets.trip.excursion.info',
                    'promocode',
                ]
            )
            ->where('id', $id)
            ->whereIn('status_id', array_merge(OrderStatus::sberpay_statuses, [OrderStatus::showcase_canceled]))
            ->whereIn('type_id', [
                OrderType::promoter_sale,
                OrderType::qr_code,
                OrderType::partner_site,
                OrderType::site,
                OrderType::referral_link,
                OrderType::partner_sale,
                OrderType::partner_sale_sms])
            ->first();

        return $order;
    }
}
