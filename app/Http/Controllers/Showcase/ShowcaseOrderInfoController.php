<?php

namespace App\Http\Controllers\Showcase;

use App\Helpers\StatisticQrCodes;
use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Jobs\ProcessShowcaseConfirmedOrder;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\PaymentStatus;
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

        if (!$order->hasStatus(OrderStatus::showcase_creating) && !$order->hasStatus(OrderStatus::showcase_wait_for_pay) && !$order->hasStatus(
                OrderStatus::showcase_confirmed
            ) && !$order->hasStatus(OrderStatus::showcase_paid)) {
            return APIResponse::error('Заказ ' . mb_strtolower($order->status->name));
        }

        $now = Carbon::now();
        $expires = Carbon::parse($orderSecret['ts'])->setTimezone($now->timezone)->addMinutes(env('SHOWCASE_ORDER_LIFETIME'));

        if ($expires < $now) {
            return APIResponse::error('Время, отведенное на оплату заказа истекло, заказ расформирован.');
        }

        // check if order is already paid
        // check order status
        $isProduction = env('SBER_ACQUIRING_PRODUCTION');
        $connection = new Connection([
            'token' => env('SBER_ACQUIRING_TOKEN'),
            'userName' => env('SBER_ACQUIRING_USER'),
            'password' => env('SBER_ACQUIRING_PASSWORD'),
        ], new CurlClient(), $isProduction);
        $options = new Options(['currency' => Currency::RUB, 'language' => 'ru']);
        $sber = new Sber($connection, $options);

        if ($order->hasStatus(OrderStatus::showcase_wait_for_pay)) {
            $checkFailed = false;
            try {
                $response = $sber->getOrderStatus($order->external_id);
            } catch (Exception $exception) {
                Log::channel('sber_payments')->error(sprintf('Order [%s] get status client error: %s', $order->id, $exception->getMessage()));
                $checkFailed = true;
            }
            if (!$checkFailed && isset($response)) {
                if ($response->isSuccess() && \App\SberbankAcquiring\OrderStatus::isDeposited($response['orderStatus'] ?? 0)) {
                    // set order status
                    $order->setStatus(OrderStatus::showcase_confirmed);
                    Log::channel('sber_payments')->info(sprintf('Order [%s] payment confirmed', $order->id));

                    // add payment
                    $payment = Payment::query()->where('gate', 'sber')->where('order_id', $order->id)->first();
                    if ($payment === null) {
                        $payment = new Payment();
                    }
                    $payment->gate = 'sber';
                    $payment->order_id = $order->id;
                    $payment->status_id = PaymentStatus::sale;
                    $payment->fiscal = '';
                    $payment->total = $response['amount'] / 100 ?? null;
                    $payment->by_card = $response['amount'] / 100 ?? null;
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
//                        Log::debug('existingCookieHash in showcaseorderinfo', $existingCookieHash);
                        if ($existingCookieHash) {
                            StatisticQrCodes::addPayment($existingCookieHash);
                        }
                    } catch (Exception $e) {
                        Log::channel('sber_payments')->error('Error with qrstatistics: ' . $e->getMessage());
                    }

                } else {
                    if (!$response->isSuccess()) {
                        Log::channel('sber_payments')->info(sprintf('Order [%s] get status error: %s', $order->id, $response->errorMessage()));
                    }
                    if (!\App\SberbankAcquiring\OrderStatus::isDeposited($response['orderStatus'] ?? 0)) {
                        $error = !empty($response['actionCodeDescription']) ? $response['actionCodeDescription'] : 'Оплата не прошла';
                        Log::channel('sber_payments')->info(sprintf('Order [%s] payment unsuccessful: %s', $order->id, $error));
                    }
                }
            }
        }

        $now = Carbon::now();
        $expires = Carbon::parse($orderSecret['ts'])->setTimezone($now->timezone)->addMinutes(env('SHOWCASE_ORDER_LIFETIME'));

        return response()->json([
            'order' => [
                'order_id' => $order->id,
                'order_status' => $order->status->name,
                'is_created' => $order->hasStatus(OrderStatus::showcase_creating),
                'is_paying' => $order->hasStatus(OrderStatus::showcase_wait_for_pay),
                'is_confirmed' => $order->hasStatus(OrderStatus::showcase_confirmed),
                'is_payed' => $order->hasStatus(OrderStatus::showcase_paid),
                'is_actual' => $expires > $now,
                'payment_page' => env('SHOWCASE_PAYMENT_PAGE') . '?order=' . $secret,
                'lifetime' => $expires > $now ? $expires->diffInSeconds($now) : null,
            ],
        ]);
    }
}
