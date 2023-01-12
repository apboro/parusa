<?php

namespace App\Http\Controllers\Checkout;

use App\Classes\EmailReceiver;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Common\Image;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\PaymentStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Payments\Payment;
use App\Models\Sails\Trip;
use App\Models\Tickets\Ticket;
use App\Notifications\OrderNotification;
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
use Illuminate\Support\Facades\Notification;
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
            'back_link' => $backLink ?? env('SHOWCASE_AP_PAGE'),
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
        $validThrough = $ts->clone()->addMinutes(env('SHOWCASE_ORDER_LIFETIME'));
        $now = Carbon::now();

        if ($order->hasStatus(OrderStatus::showcase_creating) || $order->hasStatus(OrderStatus::showcase_wait_for_pay)) {
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

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws JsonException
     */
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

        // check order status
        $isProduction = env('SBER_ACQUIRING_PRODUCTION');
        $connection = new Connection([
            'token' => env('SBER_ACQUIRING_TOKEN'),
            'userName' => env('SBER_ACQUIRING_USER'),
            'password' => env('SBER_ACQUIRING_PASSWORD'),
        ], new CurlClient(), $isProduction);
        $options = new Options(['currency' => Currency::RUB, 'language' => 'ru']);
        $sber = new Sber($connection, $options);

        try {
            $response = $sber->getOrderStatus($order->external_id);
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        if (!$response->isSuccess()) {
            return APIResponse::error($response->errorMessage());
        }

        if (!\App\SberbankAcquiring\OrderStatus::isDeposited($response['orderStatus'] ?? 0)) {
            // perform paying error handling
            return APIResponse::error($response['actionCodeDescription'] ?? 'Оплата не прошла', [$response->all()]);
        }

        // set order status
        $order->setStatus(OrderStatus::showcase_paid);
        $order->tickets->map(function (Ticket $ticket) {
            $ticket->setStatus(TicketStatus::showcase_paid);
        });

        // todo make fiscal

        // add payment
        $payment = new Payment();
        $payment->gate = 'sber';
        $payment->order_id = $order->id;
        $payment->status_id = PaymentStatus::sale;
        $payment->fiscal = '';
        $payment->total = $response['amount'] / 100 ?? null;
        $payment->by_card = $response['amount'] / 100 ?? null;
        $payment->by_cash = 0;
        $payment->external_id = $response['authRefNum'] ?? null;
        $payment->save();

        try {
            $order->payCommissions();
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }

        // email tickets
        Notification::sendNow(new EmailReceiver($order->email, $order->name), new OrderNotification($order));

        // response OK
        $backLink = $container['ref'] ?? env('SHOWCASE_AP_PAGE');
        $query = parse_url($backLink, PHP_URL_QUERY);
        if ($query) {
            $backLink .= '&status=finished';
        } else {
            $backLink .= '?status=finished';
        }

        return APIResponse::success('Заказ оплачен. Билеты высланы на электронную почту.', [
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
                ['status', 'tickets', 'tickets.grade', 'tickets.trip', 'tickets.trip.startPier', 'tickets.trip.startPier.info', 'tickets.trip.excursion', 'tickets.trip.excursion.info']
            )
            ->where('id', $id)
            ->whereIn('status_id', [OrderStatus::showcase_creating, OrderStatus::showcase_wait_for_pay, OrderStatus::showcase_paid, OrderStatus::showcase_canceled])
            ->whereIn('type_id', [OrderType::qr_code, OrderType::partner_site, OrderType::site])
            ->first();

        return $order;
    }
}
