<?php

namespace App\LifePay;

use App\Models\Order\Order;

class LifePayPayment
{
    /**
     * Make signed order data to send to payment page.
     *
     * @param Order $order
     *
     * @return  array
     */
    public static function prepare(Order $order): array
    {
        $payment = [
            'cost' => (string)$order->total(),
            'name' => 'Заказ №' . $order->id,
            'email' => $order->email,
            'service_id' => env('LIFE_PAY_IE_SERVICE_ID'),
            'order_id' => $order->id,
            'version' => '2.0',
            'comment' => 'Оплата заказа №' . $order->id,
            'payment_type' => 'spg_test',
        ];

        $url = env('LIFE_PAY_IE_URL');
        $check = LifePayCheck::calc($payment, $url);

        return array_merge($payment, [
            'check' => $check,
            'url' => $url,
        ]);
    }
}
