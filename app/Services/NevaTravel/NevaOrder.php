<?php

namespace App\Services\NevaTravel;

use App\Models\AdditionalDataOrder;
use App\Models\Dictionaries\Provider;
use App\Models\Order\Order;
use Exception;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class NevaOrder
{
    private Order $order;
    private NevaTravelRepository $nevaApiData;

    /**
     * @param NevaTravelRepository $nevaApiData
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->nevaApiData = new NevaTravelRepository();
    }

    public function make(): void
    {
        if ($this->checkOrderHasNevaTickets()) {
            if ($this->checkNevaTicketHasBackwardTicket()) {
                $result = $this->nevaApiData->makeComboOrder($this->order);
            } else {
                $result = $this->nevaApiData->makeOrder($this->order);
            }

            if (!$result || $result['status'] != 200) {
                Log::channel('neva')->error('Neva API make order error: ', [$result]);
                throw new RuntimeException('Невозможно оформить заказ на этот рейс.');
            }
            AdditionalDataOrder::create([
                'provider_id' => 10,
                'order_id' => $this->order->id,
                'provider_order_uuid' => $result['body']['id'],
            ]);

            $this->order->save();
            Log::channel('neva')->info('Neva API make order success: ', [$result, $this->order]);
        }
    }

    public function approve()
    {

        if ($this->checkOrderHasNevaTickets()) {
            $result = $this->nevaApiData->approveOrder($this->order->additionalData->provider_order_uuid);
            if (isset($result['body']['number'])) {
                $this->order->additionalData->update(['provider_order_id' => $result['body']['number']]);
            } else {
                Log::channel('neva')->error('Neva API Approve Error', [$result]);
                throw new RuntimeException('Невозможно оформить заказ на этот рейс.');
            }
        }
    }

    public function checkOrderHasNevaTickets()
    {
        $tickets = $this->order->tickets;
        foreach ($tickets as $ticket) {
            if ($ticket->provider_id == Provider::neva_travel) {
                return true;
            }
        }
        return false;
    }

    private function checkNevaTicketHasBackwardTicket()
    {
        return $this->order->tickets->filter(function ($ticket) {
            return $ticket->isBackward();
        })->isNotEmpty();
    }

    public function cancel(): void
    {
        if ($this->checkOrderHasNevaTickets()) {
            $result = $this->nevaApiData->cancelOrder(['id' => $this->order->additionalData->provider_order_uuid, 'comment' => 'Клиент потребовал возврат']);
            if (!$result || $result['status'] != 200) {
                Log::channel('neva')->error('Neva API Approve Error', [$result]);
                throw new RuntimeException('Не получилось сделать возврат.');
            }
        }
    }

    public function getOrderInfo()
    {
        return $this->nevaApiData->getOrderInfo($this->order->additionalData->provider_order_uuid);
    }
}
