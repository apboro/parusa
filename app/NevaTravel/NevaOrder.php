<?php

namespace App\NevaTravel;

use App\Http\APIResponse;
use App\Models\Order\Order;
use Exception;
use Illuminate\Support\Facades\Log;

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

    public function make()
    {
        try {
            if ($this->checkOrderHasNevaTickets()) {
                $result = $this->nevaApiData->makeOrder($this->order);
                if (!$result || $result['status'] != 200) {
                    Log::channel('neva')->error('Neva API make ticket error: ', [$result]);
                    return false;
                }
                $this->order->neva_travel_id = $result['body']['id'];
                $this->order->save();
                Log::channel('neva')->info('Neva API make ticket success: ', [$result, $this->order->neva_travel_id]);
            }
        } catch (Exception $e) {
            Log::channel('neva')->error('Neva API make ticket error: ' . $e->getMessage());
        }
        return true;
    }

    public function approve()
    {
        try {
            if ($this->checkOrderHasNevaTickets()) {
                $result = $this->nevaApiData->approveOrder($this->order->neva_travel_id);
                if ($result['body']['number']) {
                    $this->order->neva_travel_order_number = $result['body']['number'];
                    $this->order->save();
                } else {
                    Log::error('Neva API Approve Error', [$result]);
                }
            }
        } catch (Exception $e) {
            Log::error('Neva API Error: ' . $e->getMessage());
        }
    }

    public function checkOrderHasNevaTickets()
    {
        $tickets = $this->order->tickets;
        foreach ($tickets as $ticket) {
            if ($ticket->neva_travel_ticket === 1) {
                return true;
            }
        }
        return false;
    }

    public function cancel(): array
    {
        return $this->nevaApiData->cancelOrder(['id' => $this->order->neva_travel_id, 'comment' => 'Клиент потребовал возврат']);
    }

    public function getOrderInfo()
    {
        return $this->nevaApiData->getOrderInfo($this->order->neva_travel_id);
    }
}
