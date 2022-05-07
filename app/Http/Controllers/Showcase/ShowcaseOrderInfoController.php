<?php

namespace App\Http\Controllers\Showcase;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Order\Order;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use JsonException;

class ShowcaseOrderInfoController extends ApiEditController
{
    /**
     * Get order info for showcase application.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws JsonException
     */
    public function info(Request $request): JsonResponse
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

        if (!$order->hasStatus(OrderStatus::showcase_creating) && !$order->hasStatus(OrderStatus::showcase_wait_for_pay) && !$order->hasStatus(OrderStatus::showcase_paid)) {
            return APIResponse::error('Заказ ' . mb_strtolower($order->status->name));
        }

        $now = Carbon::now();
        $expires = Carbon::parse($orderSecret['ts'])->setTimezone($now->timezone)->addMinutes(env('SHOWCASE_ORDER_LIFETIME'));

        if ($expires < $now) {
            return APIResponse::error('Время, отведенное на оплату заказа илтекло, заказ расформирован.');
        }

        $now = Carbon::now();
        $expires = Carbon::parse($orderSecret['ts'])->setTimezone($now->timezone)->addMinutes(env('SHOWCASE_ORDER_LIFETIME'));

        return response()->json([
            'order' => [
                'order_id' => $order->id,
                'order_status' => $order->status->name,
                'is_created' => $order->hasStatus(OrderStatus::showcase_creating),
                'is_paying' => $order->hasStatus(OrderStatus::showcase_wait_for_pay),
                'is_payed' => $order->hasStatus(OrderStatus::showcase_paid),
                'is_actual' => $expires > $now,
                'payment_page' => env('SHOWCASE_PAYMENT_PAGE') . '?order=' . $secret,
                'lifetime' => $expires > $now ? $expires->diffInSeconds($now) : null,
            ],
        ]);
    }
}
