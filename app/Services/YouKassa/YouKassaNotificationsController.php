<?php

namespace App\Services\YouKassa;

use App\Jobs\ProcessShowcaseConfirmedOrder;
use App\Models\Dictionaries\PaymentStatus;
use App\Models\Order\Order;
use App\Models\Payments\Payment;
use Exception;
use Illuminate\Http\Request;
use YooKassa\Model\Notification\NotificationEventType;
use YooKassa\Model\Notification\NotificationFactory;


class YouKassaNotificationsController
{
    public function __invoke(Request $request)
    {
        try {
            $source = file_get_contents('php://input');
            $data = json_decode($source, true);

            $factory = new NotificationFactory();
            $notificationObject = $factory->factory($data);
            $responseObject = $notificationObject->getObject();

            $client = new \YooKassa\Client();

            if (!$client->isNotificationIPTrusted($_SERVER['REMOTE_ADDR'])) {
                header('HTTP/1.1 400 Something went wrong');
                exit();
            }

            $order = Order::find($responseObject->getMetadata()['order_id']);
            if ($notificationObject->getEvent() === NotificationEventType::PAYMENT_SUCCEEDED) {
                $someData = array(
                    'paymentId' => $responseObject->getId(),
                    'paymentStatus' => $responseObject->getStatus(),
                );

                $payment = new Payment();
                $payment->gate = 'youkassa';
                $payment->order_id = $order->id;
                $payment->status_id = PaymentStatus::sale;
                $payment->fiscal = '';
                $payment->total = $responseObject->amount->value  ?? null;
                $payment->by_card = $responseObject->amount->value  ?? null;
                $payment->by_cash = 0;
                $payment->save();

                ProcessShowcaseConfirmedOrder::dispatch($order->id);

                return response(status: 200);
            } elseif ($notificationObject->getEvent() === NotificationEventType::PAYMENT_WAITING_FOR_CAPTURE) {
                $someData = array(
                    'paymentId' => $responseObject->getId(),
                    'paymentStatus' => $responseObject->getStatus(),
                );
                // Специфичная логика
                // ...
            } elseif ($notificationObject->getEvent() === NotificationEventType::PAYMENT_CANCELED) {
                $someData = array(
                    'paymentId' => $responseObject->getId(),
                    'paymentStatus' => $responseObject->getStatus(),
                );
                // Специфичная логика
                // ...
            } elseif ($notificationObject->getEvent() === NotificationEventType::REFUND_SUCCEEDED) {
                $someData = array(
                    'refundId' => $responseObject->getId(),
                    'refundStatus' => $responseObject->getStatus(),
                    'paymentId' => $responseObject->getPaymentId(),
                );
                // ...
                // Специфичная логика
            } else {
                header('HTTP/1.1 400 Something went wrong');
                exit();
            }

            // Специфичная логика
            // ...

            $client->setAuth('367216', env('UKASSA_SECRET_KEY'));

            // Получим актуальную информацию о платеже
            if ($paymentInfo = $client->getPaymentInfo($someData['paymentId'])) {
                $paymentStatus = $paymentInfo->getStatus();
                // Специфичная логика
                // ...
            } else {
                header('HTTP/1.1 400 Something went wrong');
                exit();
            }

        } catch (Exception $e) {
            header('HTTP/1.1 400 Something went wrong');
            exit();
        }


    }

}
