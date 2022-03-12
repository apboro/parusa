<?php

namespace App\Http\Controllers\Services\LifePos;

use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Payments\Payment;
use App\Models\Tickets\Order;
use App\Models\Tickets\Ticket;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
                default:
                    Log::channel('payments')->error("LifePos: unhandled notification [{$input['type_of']}]");
            }
        } catch (Exception $exception) {
            Log::channel('payments')->error($exception->getMessage());
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
        Log::channel('payments')->info(sprintf('LifePos [Sale:%s] %s', $input['guid'], $input['status']));
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
        Log::channel('payments')->info(sprintf('LifePos [SalePayment:%s] received total - %s', $input['guid'], $input['total_sum']['value']));


        // update order status and payment data
        try {
            if (isset($input['sale']['guid'])) {
                /** @var Order $order */
                $order = Order::query()->where('external_id', $input['sale']['guid'])->first();

                // add payment
                $payment = new Payment;
                $payment->gate = 'lifepos';
                $payment->order_id = $order->id;
                $payment->fiscal = $input['fiscal_document']['guid'];
                $payment->total = $input['total_sum']['value'];
                $payment->by_card = $input['sum_by_card']['value'];
                $payment->by_cash = $input['sum_by_cash']['value'];
                $payment->external_id = $input['sale']['guid'];
                $payment->save();

                if ($order && $order->hasStatus(OrderStatus::terminal_wait_for_pay)) {

                    $order->setStatus(OrderStatus::terminal_finishing);
                    $order->tickets->map(function (Ticket $ticket) {
                        $ticket->setStatus(TicketStatus::terminal_finishing);
                    });

                } else if ($order && $order->terminal_id !== null) {

                    $order->tickets->map(function (Ticket $ticket) {
                        $ticket->setStatus(TicketStatus::terminal_paid);
                    });
                    $order->setStatus(OrderStatus::terminal_paid);

//                } else if ($order) {
//                    // todo set proper status
//                    $order->tickets->map(function (Ticket $ticket) {
//                        $ticket->setStatus(TicketStatus::terminal_paid);
//                    });
//                    $order->setStatus(OrderStatus::terminal_paid);

                } else {
                    Log::channel('payments')->error(sprintf('LifePos [SalePayment:%s] - order not found', $input['guid']));
                }
            }
        } catch (Exception $exception) {
            Log::channel('payments')->error(sprintf('LifePos [SalePayment:%s] - %s', $input['guid'], $exception->getMessage()));
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
        Log::channel('payments')->info(sprintf('LifePos [FiscalDocumentReceipt:%s] received', $input['guid']));

        // store fiscal data
        $print = $input['sources']['print_view'];
        $name = '/lifepos/' . $input['guid'] . '.txt';

        Storage::disk('fiscal')->put($name, $print);
    }
}
