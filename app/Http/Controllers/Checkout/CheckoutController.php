<?php

namespace App\Http\Controllers\Checkout;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\LifePay\LifePayCheck;
use App\LifePay\LifePayPayment;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
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
        if ($request->has('response')) {
            // Handle lifepay response
            return $this->handleResponse($request);
        }

        if ($request->has('order')) {
            // Handle initial request
            return $this->handleOrderRequest($request);
        }

        // handle page load without options
        if ($request->hasCookie(self::COOKIE_NAME)) {
            $cookies = $this->getCookies($request);
            if (!empty($cookies['ref'])) {
                return APIResponse::redirect($cookies['ref']);
            }
            return APIResponse::error('Неверно заданы параметры')->withoutCookie(self::COOKIE_NAME);
        }

        return APIResponse::error('Заказ не найден.');
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws JsonException
     */
    protected function handleOrderRequest(Request $request): JsonResponse
    {
        try {
            $container = Crypt::decrypt($request->input('order'));
            $container = json_decode($container, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            return APIResponse::error('Заказ не найден.');
        }

        // check "credentials"
        if ($container['ip'] !== $request->ip() || $container['ua'] !== $request->userAgent()) {
            return APIResponse::error('Ошибка сессии.');
        }

        // check order
        $order = $this->getOrder($container['id'] ?? null);
        if ($order === null) {
            return APIResponse::error('Заказ не найден.');
        }

        // check timestamp against order states
        $ts = Carbon::parse($container['ts']);
        $validThrough = $ts->clone()->addMinutes(15);
        $now = Carbon::now();

        if ($order->hasStatus(OrderStatus::showcase_creating) || $order->hasStatus(OrderStatus::showcase_wait_for_pay)) {
            if ($validThrough < $now) {
                return APIResponse::error('Лимит времени на оплату заказа исчерпан. Заказ аннулирован.', [
                    'backlink' => $container['ref'] ?? null,
                ]);
            }
        }

        return response()->json([
            'order' => [
                'id' => $order->id,
                'total' => $order->total(),
                'tickets' => $order->tickets->map(function (Ticket $ticket) {
                    return [
                        'id' => $ticket->id,
                        'trip_start_date' => $ticket->trip->start_at->format('d.m.Y'),
                        'trip_start_time' => $ticket->trip->start_at->format('H:i'),
                        'excursion' => $ticket->trip->excursion->name,
                        'pier' => $ticket->trip->startPier->name,
                        'grade' => $ticket->grade->name,
                        'base_price' => $ticket->base_price,
                    ];
                }),
                'name' => $order->name,
                'email' => $order->email,
                'phone' => $order->phone,
            ],
            'payment' => LifePayPayment::prepare($order),
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
        $response = $request->input('response');
        $cookies = $this->getCookies($request);

        $check = $response['check'];
        unset($response['check']);

        $valid = LifePayCheck::check($response, env('SHOWCASE_PAYMENT_PAGE'), 'get', $check);

//        if (!$valid) {
//            return APIResponse::error('Ошибка параметров.', ['backlink' => $cookies['ref'] ?? null]);
//        }

        $cookies = $this->getCookies($request);
        if (!empty($cookies['ref'])) {
            return APIResponse::redirect($cookies['ref']);
        }

        return APIResponse::error('Неверно заданы параметры')->withoutCookie(self::COOKIE_NAME);
    }

    /**
     * Set payment status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function pay(Request $request): JsonResponse
    {
        try {
            $container = Crypt::decrypt($request->input('order'));
            $container = json_decode($container, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            return APIResponse::error('Заказ не найден.');
        }

        // check "credentials"
        if ($container['ip'] !== $request->ip() || $container['ua'] !== $request->userAgent()) {
            return APIResponse::error('Ошибка сессии.');
        }

        // check order
        $order = $this->getOrder($container['id'] ?? null);
        if ($order === null) {
            return APIResponse::error('Заказ не найден.');
        }

        $order->setStatus(OrderStatus::showcase_wait_for_pay);
        $order->tickets->map(function (Ticket $ticket) {
            $ticket->setStatus(TicketStatus::showcase_wait_for_pay);
        });

        return APIResponse::success('Перенаправление на оплату...');
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
            ->with(['status', 'tickets', 'tickets.trip', 'tickets.trip.startPier', 'tickets.trip.excursion', 'tickets.grade'])
            ->where('id', $id)
            ->whereIn('status_id', [OrderStatus::showcase_creating, OrderStatus::showcase_wait_for_pay, OrderStatus::showcase_paid, OrderStatus::showcase_canceled])
            ->whereIn('type_id', [OrderType::qr_code, OrderType::partner_site, OrderType::site])
            ->first();

        return $order;
    }
}
