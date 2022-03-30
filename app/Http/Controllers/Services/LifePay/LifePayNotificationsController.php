<?php

namespace App\Http\Controllers\Services\LifePay;

use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Payments\Payment;
use App\Models\Tickets\Ticket;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class LifePayNotificationsController extends ApiController
{
    /**
     * Handle LifePos incoming notifications.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function lifePayNotification(Request $request): Response
    {
        $input = $request->all();

        try {
            switch ($input['command']) {
                case 'success':
                    $this->handleSuccess($input);
                    break;
                case 'process':
                    $this->handleProcess($input);
                    break;
                default:
                    Log::channel('lifepay_payments')->error("LifePay: unhandled notification [{$input['command']}]");
                    Log::channel('lifepay_payments')->info(json_encode($input, JSON_THROW_ON_ERROR));
            }
        } catch (Exception $exception) {
            Log::channel('lifepay_payments')->error($exception->getMessage());
            if (!empty($input)) {
                Log::channel('lifepay_payments')->info('Request content: ' . json_encode($input));
            }
        }

        return response([
            'message' => 'success',
        ], 200);
    }

    /**
     * Handel 'success' notification.
     *
     * @param array $input
     *
     * @return  void
     */
    protected function handleSuccess(array $input): void
    {
        Log::channel('lifepay_payments')->info(sprintf('LifePay [Transaction:%s] received total - %s', $input['tid'], $input['system_income']));

        // update order status and payment data
        try {
            if (isset($input['order_id'])) {
                /** @var Order $order */
                $order = Order::query()->where('id', $input['order_id'])->first();

                // add payment
                $payment = new Payment;
                $payment->gate = 'lifepay';
                $payment->status_id = 1;
                $payment->order_id = $order->id ?? null;
                // $payment->fiscal = $input['fiscal_document']['guid'];
                $payment->total = $input['system_income'];
                $payment->by_card = $input['system_income'];
                $payment->by_cash = 0;
                $payment->external_id = $input['tid'];
                $payment->save();

                if ($order && ($order->hasStatus(OrderStatus::showcase_creating) || $order->hasStatus(OrderStatus::showcase_wait_for_pay))) {

                    $order->setStatus(OrderStatus::showcase_paid);
                    $order->tickets->map(function (Ticket $ticket) {
                        $ticket->setStatus(TicketStatus::showcase_paid);
                    });
                    $order->payCommissions();

                } else {
                    Log::channel('lifepos_payments')->error(sprintf('LifePay [Transaction:%s] - order not found', $input['tid']));
                }
            }
        } catch (Exception $exception) {
            Log::channel('lifepay_payments')->error(sprintf('LifePay [Transaction:%s] - %s', $input['tid'], $exception->getMessage()));
        }
    }

    /**
     * Handel 'process' notification.
     *
     * @param array $input
     *
     * @return  void
     */
    protected function handleProcess(array $input): void
    {
        Log::channel('lifepay_payments')->info(sprintf('LifePay [Transaction:%s] processing transaction', $input['tid']));
    }
}
