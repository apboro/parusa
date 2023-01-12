<?php

namespace App\Http\Controllers\Checkout;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;
use App\SberbankAcquiring\Connection;
use App\SberbankAcquiring\Helpers\Currency;
use App\SberbankAcquiring\Helpers\MeasurementUnit;
use App\SberbankAcquiring\Helpers\PaymentMethodType;
use App\SberbankAcquiring\Helpers\PaymentObject;
use App\SberbankAcquiring\Helpers\TaxSystem;
use App\SberbankAcquiring\Helpers\TaxType;
use App\SberbankAcquiring\HttpClient\CurlClient;
use App\SberbankAcquiring\Options;
use App\SberbankAcquiring\Sber;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use JsonException;

class CheckoutInitPayController extends ApiController
{
    /**
     * Create order in sber and get payment page.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     * @throws JsonException
     */
    public function pay(Request $request): JsonResponse
    {
        $secret = $request->input('secret');
        try {
            $container = Crypt::decrypt($secret);
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

        // create order
        $orderId = $order->id . ' (' . Carbon::now()->format('d.m.Y H:i:s') . ')';
        $finishedUrl = env('SHOWCASE_PAYMENT_PAGE') . '?order=' . $secret . '&status=finished';
        $phone = preg_replace('/[^\d+]/', '', $order->phone);
        // Нет документации по частичному возврату с корзиной заказов, без неё работает
        // $count = 1;
        // $items = $order->tickets->map(function (Ticket $ticket) use (&$count) {
        //     return [
        //         'positionId' => $count++,
        //         'name' => sprintf(
        //             'Билет N%s (%s) на рейс N%s, %s',
        //             $ticket->id, mb_strtolower($ticket->grade->name), $ticket->trip->id, $ticket->trip->start_at->format('d.m.Y H:i'),
        //         ),
        //         'quantity' => [
        //             'value' => 1,
        //             'measure' => MeasurementUnit::pcs,
        //         ],
        //         'itemAmount' => (int)($ticket->base_price * 100),
        //         'itemCode' => $ticket->id,
        //         'tax' => [
        //             'taxType' => TaxType::no_vat,
        //         ],
        //         'itemPrice' => (int)($ticket->base_price * 100),
        //         'itemAttributes' => [
        //             'attributes' => [
        //                 ['name' => 'paymentMethod', 'value' => PaymentMethodType::full_payment],
        //                 ['name' => 'paymentObject', 'value' => PaymentObject::service],
        //             ],
        //         ],
        //     ];
        // });
        $data = [
            'jsonParams' => [
                'email' => $order->email,
                'phone' => $phone,
            ],
            // Нет документации по частичному возврату с корзиной заказов, без неё работает
            // 'taxSystem' => TaxSystem::simplified_income_minus_expences,
            // 'orderBundle' => [
            //     'orderCreationDate' => $order->created_at->format('Y-m-d\TH:i:s'),
            //     'customerDetails' => [
            //         'email' => $order->email,
            //         //'phone' => $phone,
            //     ],
            //     'cartItems' => ['items' => $items->toArray()],
            // ],
        ];
        if (env('SBER_ACQUIRING_CALLBACK_ENABLE')) {
            $data['dynamicCallbackUrl'] = route('sberNotification');
        }

        $isProduction = env('SBER_ACQUIRING_PRODUCTION');
        $connection = new Connection([
            'token' => env('SBER_ACQUIRING_TOKEN'),
            'userName' => env('SBER_ACQUIRING_USER'),
            'password' => env('SBER_ACQUIRING_PASSWORD'),
        ], new CurlClient(), $isProduction);
        $options = new Options(['currency' => Currency::RUB, 'language' => 'ru']);
        $sber = new Sber($connection, $options);
        try {
            $response = $sber->registerOrder($orderId, $order->total() * 100, env('SHOWCASE_ORDER_LIFETIME', 0) * 60, $finishedUrl, $data);
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        if (!$response->isSuccess()) {
            return APIResponse::error($response->errorMessage());
        }

        $order->external_id = $response['orderId'];
        $order->setStatus(OrderStatus::showcase_wait_for_pay, false);
        $order->save();
        $order->tickets->map(function (Ticket $ticket) {
            // P.S. All tickets are valid for now
            $ticket->setStatus(TicketStatus::showcase_wait_for_pay);
        });

        return APIResponse::success('Перенаправление на оплату...', [
            'form_url' => $response['formUrl'],
        ]);
    }

    /**
     * Get order.
     *
     * @param int|null $id
     *
     * @return  Order|null
     */
    protected function getOrder(?int $id): ?Order
    {
        /** @var Order $order */
        $order = Order::query()
            ->with(
                ['status', 'tickets', 'tickets.grade', 'tickets.trip', 'tickets.trip.startPier', 'tickets.trip.startPier.info', 'tickets.trip.excursion', 'tickets.trip.excursion.info']
            )
            ->where('id', $id)
            ->whereIn('status_id', [OrderStatus::showcase_creating, OrderStatus::showcase_wait_for_pay, OrderStatus::showcase_paid, OrderStatus::showcase_canceled])
            ->whereIn('type_id', [OrderType::qr_code, OrderType::partner_site, OrderType::site])
            ->first();

        return $order;
    }
}
