<?php

namespace App\LifePos;

use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\PaymentStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Payments\Payment;
use App\Models\POS\Terminal;
use App\Models\Positions\StaffPositionInfo;
use App\Models\Tickets\Ticket;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JsonException;

class LifePosSync
{
    /**
     * Sync payments from LifePos.
     *
     * @param int|null $fromId
     * @param int|null $toId
     *
     * @return  void
     */
    public static function syncMissingPayments(?int $fromId = null, ?int $toId = null): void
    {
        $ordersWithoutPaymentsExternalIds = Order::query()
            ->whereNotNull('external_id')
            ->whereDoesntHave('payments', function (Builder $query) {
                $query->where('gate', 'lifepos');
            })
            ->whereIn('status_id', [OrderStatus::terminal_paid, OrderStatus::terminal_returned])
            ->select(['id', 'external_id'])
            ->when($fromId, function (Builder $query) use ($fromId) {
                $query->where('id', '>=', $fromId);
            })
            ->when($toId, function (Builder $query) use ($toId) {
                $query->where('id', '<=', $toId);
            })
            ->get()
            ->toArray();

        $updatedOrders = [];

        foreach ($ordersWithoutPaymentsExternalIds as $order) {
            try {
                $updated = self::updateOrderPayments($order['id'], $order['external_id']);

                if ($updated > 0) {
                    $updatedOrders[] = $order['id'];
                }
            } catch (Exception $exception) {
                //throw $exception;
            }
        }

        if (!empty($updatedOrders)) {
            Log::channel('lifepos_sync')->info("Updated: " . implode(', ', $updatedOrders));
        }
    }

    protected static function updateOrderPayments(int $orderId, string $externalId): int
    {
        $payments = LifePosSales::getSalePayments($externalId);

        $isPayed = false;
        $isReturned = false;

        $updated = 0;

        if (isset($payments['items']) && count($payments['items']) > 0) {
            foreach ($payments['items'] as $receivedPayment) {
                if (!isset($receivedPayment['type_of'])) {
                    continue;
                }

                // if payment exists no need to update
                if (!empty($receivedPayment['guid']) && Payment::query()->where('external_id', $receivedPayment['guid'])->count() > 0) {
                    continue;
                }

                $payment = new Payment();
                $payment->gate = 'lifepos';
                $payment->order_id = $orderId;
                $payment->fiscal = $receivedPayment['fiscal_document']['guid'] ?? null;
                $payment->total = ($receivedPayment['total_sum']['value'] ?? 0) / 100;
                $payment->by_card = ($receivedPayment['sum_by_card']['value'] ?? 0) / 100;
                $payment->by_cash = ($receivedPayment['sum_by_cash']['value'] ?? 0) / 100;
                $payment->external_id = $receivedPayment['guid'];
                $payment->created_at = Carbon::parse($receivedPayment['created_at'])->setTimezone('Europe/Moscow');

                if ($receivedPayment['type_of'] === 'SalePayment') {
                    $payment->status_id = PaymentStatus::sale;
                    $isPayed = true;
                } else if ($receivedPayment['type_of'] === 'SaleRefund') {
                    $payment->status_id = PaymentStatus::return;
                    $isReturned = true;
                }

                // get POS and cashier
                try {
                    $sale = LifePosSales::getSale($externalId);
                    $terminalExternalId = $sale['workplace']['guid'];
                    $positionExternalId = $sale['opened_by']['guid'];
                    $terminalId = Terminal::query()->where('workplace_id', $terminalExternalId)->value('id');
                    $positionId = StaffPositionInfo::query()->where('external_id', $positionExternalId)->value('position_id');
                } catch (Exception $exception) {
                    $terminalId = null;
                    $positionId = null;
                }
                $payment->terminal_id = $terminalId;
                $payment->position_id = $positionId;
                $payment->save();

                $updated++;

                if ($receivedPayment['fiscal_document']['guid']) {
                    $receipt = LifePosSales::getFiscal($receivedPayment['fiscal_document']['guid']);
                    if (isset($receipt['sources']['print_view'])) {
                        $print = $receipt['sources']['print_view'];
                        $name = '/lifepos/' . $receipt['guid'] . '.txt';
                        Storage::disk('fiscal')->put($name, $print);

                    }
                }
            }
        }

        // update order status
        if ($isPayed || $isReturned) {
            /** @var Order $order */
            $order = Order::query()->where('id', $orderId)->first();
            $order->payment_unconfirmed = false;
            $order->setStatus($isReturned ? OrderStatus::terminal_returned : OrderStatus::terminal_paid);
            Ticket::query()->where('order_id', $orderId)->update([
                'status_id' => $isReturned ? TicketStatus::terminal_returned : TicketStatus::terminal_paid,
            ]);
        }

        return $updated;
    }

    /**
     * Sync returns from LifePos.
     *
     * @return  void
     */
    public static function syncReturns(): void
    {
        $orgId = env('LIFE_POS_ORG_ID');

        // make connection client
        $client = new Client([
            'base_uri' => env('LIFE_POS_BASE_URL'),
            'timeout' => 0,
            'allow_redirects' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . env('LIFE_POS_KEY'),
                'Accept-Language' => 'ru-RU',
            ],
        ]);
        $url = "/v4/orgs/{$orgId}/deals/sales?status=Returned";
        $pageToken = null;
        $first = true;

        $ordersToUpdate = [];

        while ($first || $pageToken !== null) {
            try {
                $result = $client->get($url . ($pageToken ? "&page_token=" . $pageToken : ''));
                $response = json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
            } catch (GuzzleException $exception) {
                Log::channel('lifepos_connection')->error("Querying returns connection error: " . $exception->getMessage());
            } catch (JsonException $e) {
            }
            if (!empty($response['items'])) {
                foreach ($response['items'] as $item) {
                    /** @var Order|null $order */
                    $order = Order::query()->where('external_id', $item['guid'])->first();
                    if ($order !== null && !$order->hasStatus(OrderStatus::terminal_returned)) {
                        $ordersToUpdate[] = ['id' => $order->id, 'external_id' => $order->external_id];
                    }
                }
            }
            $first = false;
            $pageToken = $response['next_page_token'] ?? null;
        }

        if (!empty($ordersToUpdate)) {
            $updatedOrders = [];

            foreach ($ordersToUpdate as $order) {
                try {
                    $updated = self::updateOrderPayments($order['id'], $order['external_id']);

                    if ($updated > 0) {
                        $updatedOrders[] = $order['id'];
                    }
                } catch (Exception $exception) {
                    //throw $exception;
                }
            }

            if (!empty($updatedOrders)) {
                Log::channel('lifepos_sync')->info("Updated: " . implode(', ', $updatedOrders));
            }
        }
    }
}
