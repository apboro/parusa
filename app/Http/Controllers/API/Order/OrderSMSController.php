<?php

namespace App\Http\Controllers\API\Order;

use App\Http\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\HitSource;
use App\Models\Hit\Hit;
use App\Models\Order\Order;
use Illuminate\Http\Request;
use Rubium\RedSms\Facades\RedSms;

class OrderSMSController extends Controller
{
    public function sendSMS(Request $request)
    {
        Hit::register(HitSource::crm);
        $order = Order::find($request->orderId);
        if ($order) {
            $result = RedSms::send($order->phone, 'Открыть заказ: ' . config('app.url') . '/external/order/' . $order->hash);
        } else {
            return APIResponse::error('Заказ не найден');
        }

        if ($result) {
            return APIResponse::success('СМС сообщение отправлено');
        } else {
            return APIResponse::error('Не удалось отправить СМС');
        }
    }

    public function sendPaymentLinkSMS(Request $request)
    {
        Hit::register(HitSource::crm);
        $order = Order::find($request->input('orderId'));

        if ($order) {
            $result = RedSms::send($order->phone, 'Оплатить - '. config('app.url') . '/ext/order/payment/' . $order->hash );
        } else {
            return APIResponse::error('Заказ не найден');
        }

        if ($result) {
            return APIResponse::success('СМС сообщение отправлено');
        } else {
            return APIResponse::error('Не удалось отправить СМС');
        }

    }
}
