<?php

namespace App\LifePos;

use App\Events\CityTourOrderPaidEvent;
use App\Events\NevaTravelOrderPaidEvent;
use App\Jobs\FakeLifePosNotification;
use App\LifePos\LifePosSales;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\PaymentStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Payments\Payment;
use App\Models\POS\Terminal;
use App\Models\Positions\Position;
use App\Models\Tickets\Ticket;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class MockLifePos
{
    public static $mockResponseData = [];

    public static function send(Order $order): void
    {
        $order->external_id = 'test-' . Str::random('12');
        $order->save();

        FakeLifePosNotification::dispatch($order);
    }

    public static function cancel(Order $order): void
    {
        self::$mockResponseData['cancel'] = ['status' => 'success', 'message' => 'Order canceled successfully'];
    }

    public static function getSale(string $guid): array
    {
        self::$mockResponseData['getSale'] = [
            'workplace' => ['guid' => $guid],
            'opened_by' => ['guid' =>$guid],
            'status' => 'success', 'message' => 'Sale info retrieved successfully'];
        return self::$mockResponseData['getSale'];
    }

    public static function getSalePayments(string $guid): array
    {
        self::$mockResponseData['getSalePayments'] = [
            'items' => [
                [
                    'type_of' => 'SalePayment',
                    'guid' => $guid,
                    'fiscal_document' => ['guid' => null]
                ]
            ]
        ];
        return self::$mockResponseData['getSalePayments'];
    }

    public static function SalePaymentNotification (Order $order)
    {
        if ($order->hasStatus(OrderStatus::terminal_wait_for_pay)
            || $order->hasStatus(OrderStatus::terminal_wait_for_pay_from_reserve)) {

            $order->payment_unconfirmed = false;
            $order->setStatus(OrderStatus::terminal_finishing);
            $order->tickets->map(function (Ticket $ticket) {
                $ticket->setStatus(TicketStatus::terminal_finishing);
            });
            Log::channel('neva')->info('terminal_finishing');
            NevaTravelOrderPaidEvent::dispatch($order);
            CityTourOrderPaidEvent::dispatch($order);


        } else if ($order->terminal_id !== null && !$order->hasStatus(OrderStatus::terminal_finishing)) {

            $order->payment_unconfirmed = false;
            $order->tickets->map(function (Ticket $ticket) {
                $ticket->setStatus(TicketStatus::terminal_paid);
            });
            $order->setStatus(OrderStatus::terminal_paid);

            Log::channel('neva')->info('terminal_paid');
            NevaTravelOrderPaidEvent::dispatch($order);
            CityTourOrderPaidEvent::dispatch($order);
        }

        $payment = Payment::query()->where('order_id', $order->id)->first();

        if (!isset($payment))
        {
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
        $payment->terminal_id = $terminalId ?? null;
        $payment->position_id = $positionId ?? null;
        $payment->save();
    }
}

