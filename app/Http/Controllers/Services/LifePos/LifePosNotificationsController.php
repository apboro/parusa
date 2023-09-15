<?php

namespace App\Http\Controllers\Services\LifePos;

use App\Events\CityTourCancelOrderEvent;
use App\Events\CityTourOrderPaidEvent;
use App\Events\NevaTravelCancelOrderEvent;
use App\Events\NevaTravelOrderPaidEvent;
use App\Helpers\Fiscal;
use App\Http\Controllers\ApiController;
use App\LifePos\LifePosSales;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\PaymentStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Payments\Payment;
use App\Models\POS\Terminal;
use App\Models\Positions\StaffPositionInfo;
use App\Models\Tickets\Ticket;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class LifePosNotificationsController extends ApiController
{
    /**
     * Handle LifePos incoming notifications.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function lifePosNotification(Request $request): Response
    {
        $input = $request->all();

        try {
            switch ($input['type_of']) {
                case 'Sale':
                    $this->handleSale($input);
                    break;
                case 'FiscalDocumentReceipt':
                    $this->handleFiscalDocumentReceipt($input);
                    break;
                case 'SalePayment':
                    $this->handleSalePayment($input);
                    break;
                case 'SaleRefund':
                    $this->handleSaleRefund($input);
                    break;
                default:
                    Log::channel('lifepos_payments')->info("LifePos: unhandled notification [{$input['type_of']}]");
                    Log::channel('lifepos_payments')->info(json_encode($input));
            }
        } catch (Exception $exception) {
            Log::channel('lifepos_payments')->error($exception->getMessage());
            if (!empty($input)) {
                Log::channel('lifepos_payments')->info('Request content: ' . json_encode($input));
            }
        }

        return response([
            'message' => 'success',
        ], 200);
    }

    /**
     * Handel 'Sale' notification.
     *
     * @param array $input
     *
     * @return void
     */
    protected function handleSale(array $input): void
    {
        Log::channel('lifepos_payments')->info(sprintf('LifePos [Sale:%s] status -> %s', $input['guid'], $input['status']));
    }

    /**
     * Handel 'SalePayment' notification.
     *
     * @param array $input
     *
     * @return  void
     */
    protected function handleSalePayment(array $input): void
    {
        Log::channel('lifepos_payments')->info(sprintf('LifePos [SalePayment:%s] received total - %s', $input['guid'], $input['total_sum']['value']));

        // update order status and payment data
        try {
            if (isset($input['sale']['guid'])) {
                $externalId = $input['sale']['guid'];

                /** @var Order|null $order */
                $order = Order::query()->where('external_id', $externalId)->first();

                // get POS and cashier
                try {
                    $sale = LifePosSales::getSale($externalId);
                    $terminalExternalId = $sale['workplace']['guid'];
                    $positionExternalId = $sale['opened_by']['guid'];
                    $terminalId = Terminal::query()->where('workplace_id', $terminalExternalId)->value('id');
                    $positionId = StaffPositionInfo::query()->where('external_id', $positionExternalId)->value('position_id');
                } catch (Exception $exception) {
                    Log::channel('lifepos_payments')->error(sprintf('LifePos [getSale]  error: %s', $exception->getMessage()));
                    $terminalId = null;
                    $positionId = null;
                }

                // search existing payment to update
                if (!empty($input['guid'])) {
                    /** @var Payment|null $payment */
                    $payment = Payment::query()->where('external_id', $input['guid'])->first();
                }
                // if no payment to update create new
                if (!isset($payment)) {
                    $payment = new Payment;
                }

                $payment->gate = 'lifepos';
                $payment->status_id = PaymentStatus::sale;
                $payment->order_id = $order->id ?? null;
                $payment->fiscal = $input['fiscal_document']['guid'] ?? null;
                $payment->total = ($input['total_sum']['value'] ?? 0) / 100;
                $payment->by_card = ($input['sum_by_card']['value'] ?? 0) / 100;
                $payment->by_cash = ($input['sum_by_cash']['value'] ?? 0) / 100;
                $payment->external_id = $input['guid'];
                $payment->terminal_id = $terminalId;
                $payment->position_id = $positionId;
                $payment->save();

                if ($order && ($order->hasStatus(OrderStatus::terminal_wait_for_pay) || $order->hasStatus(OrderStatus::terminal_wait_for_pay_from_reserve))) {

                    $order->payment_unconfirmed = false;
                    $order->setStatus(OrderStatus::terminal_finishing);
                    $order->tickets->map(function (Ticket $ticket) {
                        $ticket->setStatus(TicketStatus::terminal_finishing);
                    });
                    Log::channel('neva')->info('terminal_finishing');
                    NevaTravelOrderPaidEvent::dispatch($order);
                    CityTourOrderPaidEvent::dispatch($order);


                } else if ($order && $order->terminal_id !== null && !$order->hasStatus(OrderStatus::terminal_finishing)) {

                    $order->payment_unconfirmed = false;
                    $order->tickets->map(function (Ticket $ticket) {
                        $ticket->setStatus(TicketStatus::terminal_paid);
                    });
                    $order->setStatus(OrderStatus::terminal_paid);

                    Log::channel('neva')->info('terminal_paid');
                    NevaTravelOrderPaidEvent::dispatch($order);
                    CityTourOrderPaidEvent::dispatch($order);

                } else {
                    Log::channel('lifepos_payments')->error(sprintf('LifePos [SalePayment:%s] - order not found', $input['guid']));
                    Log::channel('lifepos_payments')->info('Request content: ' . json_encode($input));
                }
            } else {
                Log::channel('lifepos_payments')->error(sprintf('LifePos [SalePayment]  error: GUID not set. Request content: %s', json_encode($input)));
            }
        } catch (Exception $exception) {
            Log::channel('lifepos_payments')->error(sprintf('LifePos [SalePayment:%s] - %s', $input['guid'], $exception->getMessage()));
            Log::channel('lifepos_payments')->info('Request content: ' . json_encode($input));
        }
    }

    /**
     * Handel 'FiscalDocumentReceipt' notification.
     *
     * @param array $input
     *
     * @return void
     */
    protected function handleFiscalDocumentReceipt(array $input): void
    {
        Log::channel('lifepos_payments')->info(sprintf('LifePos [FiscalDocumentReceipt:%s] fiscal received', $input['guid']));

        // store fiscal data
        $print = $input['sources']['print_view'];

        Fiscal::put('lifepos', $input['guid'], $print);
    }

    /**
     * Handel 'SalePayment' notification.
     *
     * @param array $input
     *
     * @return  void
     */
    protected function handleSaleRefund(array $input): void
    {
        Log::channel('lifepos_payments')->info(sprintf('LifePos [SaleRefund:%s] for payment %s - returned total: %s', $input['guid'], $input['for_payment']['guid'], $input['total_sum']['value']));

        /** @var Payment|null $parent */
        $parent = Payment::query()->where('external_id', $input['for_payment']['guid'])->first();
        /** @var Order|null $order */
        $order = $parent ? Order::query()->where('id', $parent->order_id)->first() : null;

        $externalId = $input['sale']['guid'];

        // get POS and cashier
        try {
            $sale = LifePosSales::getSale($externalId);
            $terminalExternalId = $sale['workplace']['guid'];
            $positionExternalId = $sale['opened_by']['guid'];
            $terminalId = Terminal::query()->where('workplace_id', $terminalExternalId)->value('id');
            $positionId = StaffPositionInfo::query()->where('external_id', $positionExternalId)->value('position_id');
        } catch (Exception $exception) {
            Log::channel('lifepos_payments')->error(sprintf('LifePos [getSale]  error: %s', $exception->getMessage()));
            $terminalId = null;
            $positionId = null;
        }

        // search existing payment to update
        if (!empty($input['guid'])) {
            /** @var Payment|null $payment */
            $payment = Payment::query()->where('external_id', $input['guid'])->first();
        }
        // if no payment to update create new
        if (!isset($payment)) {
            $payment = new Payment;
        }

        $payment->gate = 'lifepos';
        $payment->status_id = PaymentStatus::return;
        $payment->parent_id = $parent->id ?? null;
        $payment->order_id = $order->id ?? null;
        $payment->fiscal = $input['fiscal_document']['guid'] ?? null;
        $payment->total = ($input['total_sum']['value'] ?? 0) / 100;
        $payment->by_card = ($input['sum_by_card']['value'] ?? 0) / 100;
        $payment->by_cash = ($input['sum_by_cash']['value'] ?? 0) / 100;
        $payment->external_id = $input['guid'] ?? null;
        $payment->terminal_id = $terminalId;
        $payment->position_id = $positionId;
        $payment->save();

        if ($order) {
            $order->setStatus(OrderStatus::terminal_returned);
            $order->tickets->map(function (Ticket $ticket) {
                $ticket->refundCommission();
                $ticket->setStatus(TicketStatus::terminal_returned);
            });

            NevaTravelCancelOrderEvent::dispatch($order);
            CityTourCancelOrderEvent::dispatch($order);

        } else {
            Log::channel('lifepos_payments')->error(sprintf('LifePos [SaleRefund:%s] - order not found', $input['guid']));
            Log::channel('lifepos_payments')->info('Request content: ' . json_encode($input));
        }
    }
}
