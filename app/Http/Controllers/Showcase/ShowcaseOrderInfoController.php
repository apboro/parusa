<?php

namespace App\Http\Controllers\Showcase;

use App\Helpers\StatisticQrCodes;
use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Jobs\ProcessShowcaseConfirmedOrder;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\PartnerType;
use App\Models\Dictionaries\PaymentStatus;
use App\Models\Hit\Hit;
use App\Models\Order\Order;
use App\Models\Payments\Payment;
use App\SberbankAcquiring\Connection;
use App\SberbankAcquiring\Helpers\Currency;
use App\SberbankAcquiring\HttpClient\CurlClient;
use App\SberbankAcquiring\Options;
use App\SberbankAcquiring\Sber;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use JsonException;
use YooKassa\Client;

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
        Hit::register(HitSource::showcase);
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

        if (!in_array($order->status_id, OrderStatus::sberpay_statuses)) {
            return APIResponse::error('Заказ ' . mb_strtolower($order->status->name));
        }

        $now = Carbon::now();
        $expires = Carbon::parse($orderSecret['ts'])->setTimezone($now->timezone)->addMinutes(config('showcase.showcase_order_lifetime'));

        if ($expires < $now) {
            return APIResponse::error('Время, отведенное на оплату заказа истекло, заказ расформирован.');
        }

        $client = new Client();
        $client->setAuth(config('youkassa.shop_id'), config('youkassa.secret_key'));

        if ($order->hasStatus(OrderStatus::showcase_wait_for_pay) || $order->hasStatus(OrderStatus::promoter_wait_for_pay)) {
            $checkFailed = false;

            $client = new \YooKassa\Client();
            $client->setAuth(config('youkassa.shop_id'), config('youkassa.secret_key'));

            $paymentId = $order->external_id;
            try {
                $response = $client->getPaymentInfo($paymentId);
            } catch (Exception $exception) {
                Log::channel('youkassa')->error(sprintf('Order [%s] get status client error: %s', $order->id, $exception->getMessage()));
                $checkFailed = true;
            }
            if (!$checkFailed && isset($response)) {
                if ($response['status'] === 'succeeded') {
                    // set order status
                    $newOrderStatus = $order->type_id === OrderType::promoter_sale ? OrderStatus::promoter_confirmed : OrderStatus::showcase_confirmed;
                    $order->setStatus($newOrderStatus);
                    Log::channel('youkassa')->info(sprintf('Order [%s] payment confirmed', $order->id));

                    // add payment
                    $payment = Payment::query()->where('gate', 'youkassa')->where('order_id', $order->id)->first();
                    if ($payment === null) {
                        $payment = new Payment();
                    }
                    $payment->gate = 'youkassa';
                    $payment->order_id = $order->id;
                    $payment->status_id = PaymentStatus::sale;
                    $payment->fiscal = '';
                    $payment->total = $response['amount']['value'] ?? null;
                    $payment->by_card = $response['amount']['value'] ?? null;
                    $payment->by_cash = 0;
                    $payment->save();

                    $order->refresh();

                    // Make job to do in background:
                    // make fiscal
                    // send tickets
                    // pay commission
                    // update order status
                    ProcessShowcaseConfirmedOrder::dispatch($order->id);

                    try {
                        $existingCookieHash = $request->cookie('qrCodeHash');
                        Log::channel('qr-codes')->info('existingCookieHash in showcaseorderinfo', [$existingCookieHash]);
                        if ($existingCookieHash) {
                            StatisticQrCodes::addPayment($existingCookieHash);
                        }
                    } catch (Exception $e) {
                        Log::channel('youkassa')->error('Error with qrstatistics: ' . $e->getMessage());
                    }
                } else {
                    Log::channel('youkassa')->info(sprintf('Order [%s] get status error: %s', $order->id, $response->getStatus()));
                }
            }
        }

        $now = Carbon::now();
        $expires = Carbon::parse($orderSecret['ts'])->setTimezone($now->timezone)->addMinutes(config('showcase.showcase_order_lifetime'));

        return response()->json([
            'order' => [
                'order_from_promoter' => $order->partner?->type_id === PartnerType::promoter,
                'order_id' => $order->id,
                'order_status' => $order->status->name,
                'is_created' => $order->hasStatus(OrderStatus::showcase_creating) || $order->hasStatus(OrderStatus::promoter_wait_for_pay),
                'is_paying' => $order->hasStatus(OrderStatus::showcase_wait_for_pay) || $order->hasStatus(OrderStatus::promoter_wait_for_pay),
                'is_confirmed' => $order->hasStatus(OrderStatus::showcase_confirmed) || $order->hasStatus(OrderStatus::promoter_confirmed),
                'is_payed' => $order->hasStatus(OrderStatus::showcase_paid) || $order->hasStatus(OrderStatus::promoter_paid),
                'is_actual' => $expires > $now,
                'payment_page' => config('showcase.showcase_payment_page') . '?order=' . $secret,
                'lifetime' => $expires > $now ? $expires->diffInSeconds($now) : null,
            ],
        ]);
    }
}
