<?php

namespace App\Http\Controllers\Checkout;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use JsonException;

class CheckoutInitController extends ApiController
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
    public function init(Request $request): JsonResponse
    {
        // Get order container (from request or cookie)
        $external = $request->has('order');
        $container = $external ? $request->input('order') : $request->cookie(self::COOKIE_NAME);

        // Decode, parse order container and get corresponding order
        try {
            $container = $external ? Crypt::decrypt($container) : $container;
            $container = json_decode($container, true, 512, JSON_THROW_ON_ERROR);
            /** @var Order $order */
            $order = Order::query()
                ->with(['status', 'tickets', 'tickets.trip', 'tickets.trip.startPier', 'tickets.trip.excursion', 'tickets.grade'])
                ->where('id', $container['id'])
                ->whereIn('status_id', [OrderStatus::showcase_creating, OrderStatus::showcase_wait_for_pay, OrderStatus::showcase_paid, OrderStatus::showcase_canceled])
                ->whereIn('type_id', [OrderType::qr_code, OrderType::partner_site, OrderType::site])
                ->first();
        } catch (Exception $exception) {
            return APIResponse::error('Заказ не найден.');
        }

        // check "credentials"
        if ($container['ip'] !== $request->ip() || $container['ua'] !== $request->userAgent()) {
            return APIResponse::error('Ошибка сессии.');
        }

        // check order
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
            'payment' => $this->signedPayment($order),
            'lifetime' => $validThrough > $now ? $validThrough->diffInSeconds($now) : null,

        ])->withCookie(cookie(self::COOKIE_NAME, json_encode($container, JSON_THROW_ON_ERROR)));
    }

    protected function signedPayment(Order $order): array
    {
        $payment = [
            'cost' => (string)$order->total(),
            'name' => 'Заказ №' . $order->id,
            'email' => $order->email,
            'service_id' => env('LIFE_PAY_IE_SERVICE_ID'),
            'order_id' => $order->id,
            'version' => '2.0',
            'comment' => 'Оплата заказа №' . $order->id,
            // 'payment_type' => 'spg_test',
            // 'invoice_data' => '{"items":[{"name":"Покупка электронного билета", "price":1865.00, "unit":"piece", "quantity":1, "sum":1865.00, "vat_mode":"none"}]}',
        ];

        $url = 'https://partner.life-pay.ru/alba/input/';
        $check = $this->sign($payment, $url);

        return array_merge($payment, [
            'check' => $check,
            'url' => $url,
        ]);
    }

    protected function sign(array $params, string $url): string
    {
        ksort($params, SORT_LOCALE_STRING);

        $parsed = parse_url($url);
        $path = $parsed['path'];
        $host = $parsed['host'] ?? '';

        $data = implode("\n", ['POST', $host, $path, $this->httpQueryRFC3986($params)]);

        $secret = env('LIFE_PAY_IE_SECRET');

        return base64_encode(hash_hmac("sha256", $data, $secret, true));
    }

    protected function httpQueryRFC3986(array $data, string $separator = '&'): string
    {
        $arguments = [];
        foreach ($data as $key => $argument) {
            $arguments[] = $key . '=' . rawurlencode($argument);
        }
        return implode($separator, $arguments);
    }
}
