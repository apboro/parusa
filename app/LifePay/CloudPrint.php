<?php

namespace App\LifePay;

use App\Models\Order\Order;
use App\Models\Payments\Payment;
use App\Models\Tickets\Ticket;
use Exception;
use Illuminate\Support\Facades\Log;

class CloudPrint
{
    public const payment = 'payment';

    public const refund = 'refund';

    /**
     * Register receipt.
     *
     * https://apidoc.life-pay.ru/cloud-print/index
     *
     * @param Order $order
     * @param array $tickets
     * @param string $type
     * @param Payment $payment
     *
     * @return void
     */
    public static function createReceipt(Order $order, array $tickets, string $type, Payment $payment): void
    {
        $receipt = [
            'login' => env('CLOUD_PRINT_LOGIN'),
            'apikey' => env('CLOUD_PRINT_API_KEY'),
            'mode' => 'email',
            'test' => env('CLOUD_PRINT_TEST') ? 1 : 0,
            'target_serial' => env('CLOUD_PRINT_SERIAL'),
            'target_service' => 'lifepayCloud',
            'type' => $type,
            'customer_email' => $order->email,
            'customer_name' => $order->name,
            'order_number' => (string)$order->id,
            'card_amount' => 0,
            'payment_place' => $order->terminal?->pier?->info?->address ?? 'Адмиралтейская наб. д. 16',
            'purchase' => [
                'products' => [],
            ],
            'callback_url' => route('cloudPrintNotification'),
        ];
        foreach ($tickets as $ticket) {
            /** @var Ticket $ticket */
            $ticket->loadMissing(['grade', 'trip.excursion', 'order', 'order.promocode']);
            $ticketPrice = $ticket->getPrice();
            $item['quantity'] = 1;
            $item['price'] = $ticketPrice;
            $receipt['card_amount'] += $ticketPrice;
            $item['tax'] = 'none'; // НДС не облагается
            $item['unit'] = 'piece'; // штуки
            $item['type'] = 4; // Полная предварительная оплата до момента передачи предмета расчета
            $item['item_type'] = 4; // услуга
            $item['name'] = 'Билет №' . $ticket->id . ' (' . mb_strtolower($ticket->grade->name) . ')  на экскурсию ' . $ticket->trip->excursion->name
                . ', рейс №' . $ticket->trip->id . ', ' . $ticket->trip->start_at->format('d.m.Y H:i');

            $receipt['purchase']['products'][] = $item;
        }

        try {
            $request = json_encode($receipt);
            $curl = curl_init();

                curl_setopt($curl, CURLOPT_URL, env('CLOUD_PRINT_URL') . 'create-receipt');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request);

            $result = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($result, true);

            if ($response['code'] === 0 && isset($response['data'], $response['data']['uuid'])) {
                // log fiscal success
                $payment->fiscal = $response['data']['uuid'];
                $payment->save();
                Log::channel('cloud_print')->info(sprintf('Order [%s] fiscal registered: %s', $order->id, $response['data']['uuid']));
            } else {
                // log fiscal error
                Log::channel('cloud_print')->error(sprintf('Order [%s] fiscal registration error: %s', $order->id, json_encode($response['data'] ?? [])));
            }
        } catch (Exception $exception) {
            //log error
            Log::channel('cloud_print')->error(sprintf('Order [%s] fiscal error: %s', $order->id, $exception->getMessage()));
        }
    }
}
