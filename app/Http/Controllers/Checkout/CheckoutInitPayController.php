<?php

namespace App\Http\Controllers\Checkout;

use App\Helpers\StatisticQrCodes;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Hit\Hit;
use App\Models\Order\Order;
use App\Models\Partner\Partner;
use App\Models\QrCodes\QrCode;
use App\Models\Tickets\Ticket;
use App\SberbankAcquiring\Connection;
use App\SberbankAcquiring\Helpers\Currency;
use App\SberbankAcquiring\HttpClient\CurlClient;
use App\SberbankAcquiring\Options;
use App\SberbankAcquiring\Sber;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use JsonException;
use YooKassa\Client;
use YooKassa\Model\Payment\ConfirmationType;
use YooKassa\Request\Payments\CreatePaymentRequest;

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
        Hit::register(HitSource::checkout);
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
        $finishedUrl = config('showcase.showcase_payment_page') . '?order=' . $secret. '&status=finished';
        $phone = preg_replace('/[^\d+]/', '', $order->phone);

        $client = new Client();
        $client->setAuth(config('youkassa.shop_id'), config('youkassa.secret_key'));
        $builder = CreatePaymentRequest::builder();
        $builder->setAmount($order->total())
            ->setCurrency(\YooKassa\Model\CurrencyCode::RUB)
            ->setCapture(true)
            ->setDescription('Оплата заказа '. $order->id)
            ->setMetadata([
                'cms_name'       => 'Алые Паруса',
                'order_id'       => $order->id,
                'language'       => 'ru',
            ]);

        $builder->setConfirmation(array(
            'type'      => ConfirmationType::REDIRECT,
            'returnUrl' => $finishedUrl,
        ));

        $youkassaRequest = $builder->build();
        $idempotenceKey = uniqid('', true);

        try {
            $response = $client->createPayment($youkassaRequest, $idempotenceKey);
        } catch (Exception $exception) {
            Log::channel('youkassa')->error(sprintf('Order [%s] registration client error: %s', $orderId, $exception->getMessage()));
            return APIResponse::error($exception->getMessage());
        }

        if ($response->getStatus() !== 'pending') {
            Log::channel('youkassa')->error(sprintf('Order [%s] registration error: %s', $orderId, $response->getDescription()));
            return APIResponse::error($response->getDescription());
        }

        $order->external_id = $response->getId();
        if ($order->status_id != OrderStatus::promoter_wait_for_pay) {
            $order->setStatus(OrderStatus::showcase_wait_for_pay, false);
        }
        $order->save();

        Log::channel('sber_payments')->info(sprintf('Order [%s] registered [ID:%s]', $orderId, $order->external_id));

        $order->tickets->map(function (Ticket $ticket) {
            // P.S. All tickets are valid for now
            if ($ticket->status_id != TicketStatus::promoter_wait_for_pay) {
                $ticket->setStatus(TicketStatus::showcase_wait_for_pay);
            }
        });

        if (!in_array($order->status_id, [OrderStatus::partner_wait_for_pay, OrderStatus::promoter_wait_for_pay])) {

            $existingCookieHash = $request->cookie('qrCodeHash');
            try {
                if ($existingCookieHash) {
                    /** @var QrCode|null $qrCode */
                    $qrCode = QrCode::query()->where('hash', $existingCookieHash)->first();
                    if ($qrCode) {
                        $order->partner_id = $qrCode->partner_id;
                        $order->type_id = OrderType::qr_code;
                        StatisticQrCodes::addPayment($existingCookieHash);
                    }
                }
            } catch (Exception $e) {
                Log::channel('youkassa')->error('Error with qr statistics: ' . $e->getMessage());
            }

            $referralCookie = $request->cookie('referralLink');
            try {
                if ($referralCookie) {
                    /**@var Partner|null $partner */
                    $partner = Partner::query()->where('id', $referralCookie)->first();
                    if ($partner) {
                        $order->partner_id = $partner->id;
                        $order->type_id = OrderType::referral_link;
                    }
                }
            } catch (Exception $e) {
                Log::channel('youkassa')->error('Error with referral statistics: ' . $e->getMessage());
            }
            $order->save();
        }

        return APIResponse::success('Перенаправление на оплату...', [
            'form_url' => $response->getConfirmation()['_confirmation_url'],
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
            ->whereIn('status_id', array_merge(OrderStatus::sberpay_statuses, [OrderStatus::showcase_canceled]))
            ->whereIn('type_id', [
                OrderType::promoter_sale,
                OrderType::qr_code,
                OrderType::partner_site,
                OrderType::site,
                OrderType::partner_sale,
                OrderType::referral_link,
                OrderType::partner_sale_sms])
            ->first();

        return $order;
    }
}
