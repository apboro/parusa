<?php

namespace App\Services\YouKassa;

use Exception;
use Illuminate\Http\Request;
use YooKassa\Model\Notification\NotificationEventType;
use YooKassa\Model\Notification\NotificationFactory;


class YouKassaNotificationsController
{
    public function __invoke(Request $request): void
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

            if ($notificationObject->getEvent() === NotificationEventType::PAYMENT_SUCCEEDED) {
                $someData = array(
                    'paymentId' => $responseObject->getId(),
                    'paymentStatus' => $responseObject->getStatus(),
                );
                // Специфичная логика
                // ...
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

            $client->setAuth('xxxxxx', 'test_XXXXXXX');

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
