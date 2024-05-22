<?php

namespace App\Console\Commands;

use App\Jobs\ProcessShowcaseConfirmedOrder;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
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
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncShowcasePayWaiting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:showcase_pay_waiting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync showcase orders with pay waiting status';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $now = Carbon::now();

        $orders = Order::query()
            ->whereIn('status_id', [OrderStatus::showcase_wait_for_pay, OrderStatus::promoter_wait_for_pay])
            ->where('updated_at', '<', $now->clone()->addMinutes(-10))
            ->get();

        if ($orders->count() === 0) {
            return 0;
        }

        $client = new \YooKassa\Client();
        $client->setAuth(config('youkassa.shop_id'), config('youkassa.secret_key'));

        foreach ($orders as $order) {
            /** @var Order $order */
            if ($order->external_id !== null) {
                $paymentId = $order->external_id;
                try {
                    $response = $client->getPaymentInfo($paymentId);
                } catch (Exception $exception) {
                    Log::channel('youkassa')->error($exception->getMessage());
                    continue;
                }
                if ($response['status'] === 'succeeded') {
                    // set order status
                    $order->status_id = $order->type_id === OrderType::promoter_sale ? OrderStatus::promoter_confirmed : OrderStatus::showcase_confirmed;

                    // add payment
                    $payment = new Payment();
                    $payment->gate = 'youkassa';
                    $payment->order_id = $order->id;
                    $payment->status_id = PaymentStatus::sale;
                    $payment->fiscal = '';
                    $payment->total = $response['amount'] / 100 ?? null;
                    $payment->by_card = $response['amount'] / 100 ?? null;
                    $payment->by_cash = 0;
                    $payment->save();

                    // Make job to do in background:
                    // make fiscal
                    // send tickets
                    // pay commission
                    // update order status
                    ProcessShowcaseConfirmedOrder::dispatch($order->id);
                }
            }
        }

        return 0;
    }
}
