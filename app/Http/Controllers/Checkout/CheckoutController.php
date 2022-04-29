<?php

namespace App\Http\Controllers\Checkout;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\LifePay\LifePayCheck;
use App\Models\Common\Image;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Sails\Trip;
use App\Models\Tickets\Ticket;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
     */
    public function handle(Request $request): JsonResponse
    {
        // Handle initial request
        if ($request->has('order')) {
            return $this->handleOrderRequest($request);
        }

//        if ($request->has('response')) {
        // Handle lifepay response
//            return $this->handleResponse($request);
//        }


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
                    'backlink' => $container['ref'] ?? null,
                ]);
            }
        }

        $trips = new Collection;

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
        if ($container['ip'] !== $request->ip()) {
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
            ->with(['status', 'tickets', 'tickets.grade', 'tickets.trip', 'tickets.trip.startPier', 'tickets.trip.startPier.info', 'tickets.trip.excursion', 'tickets.trip.excursion.info'])
            ->where('id', $id)
            ->whereIn('status_id', [OrderStatus::showcase_creating, OrderStatus::showcase_wait_for_pay, OrderStatus::showcase_paid, OrderStatus::showcase_canceled])
            ->whereIn('type_id', [OrderType::qr_code, OrderType::partner_site, OrderType::site])
            ->first();

        return $order;
    }
}
