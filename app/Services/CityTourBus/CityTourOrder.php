<?php

namespace App\Services\CityTourBus;

use App\Models\AdditionalDataOrder;
use App\Models\Dictionaries\Provider;
use App\Models\Model;
use App\Models\Order\Order;
use Exception;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CityTourOrder
{
    private Order $order;
    private CityTourRepository $cityTourRepository;


    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->cityTourRepository = new CityTourRepository();
    }

    public function make()
    {
        if ($this->checkOrderHasCityTourTickets()) {
            $result = $this->cityTourRepository->makeOrder($this->order);
            if (!$result || $result['status'] != 200) {
                Log::channel('city_tour')->error('City Tour API make order error: ', [$result]);
                throw new RuntimeException('Невозможно оформить заказ на этот рейс.');
            }

            $orderData = AdditionalDataOrder::query()->where(['order_id' => $this->order->id])->first();
            if ($orderData) {
                $orderData->provider_id = Provider::city_tour;
                $orderData->provider_order_id = $result['body']['order_id'];
            } else {
                $orderData = new AdditionalDataOrder();
                $orderData->provider_id = Provider::city_tour;
                $orderData->provider_order_id = $result['body']['order_id'];
                $orderData->order_id = $this->order->id;
            }
            $this->order->save();
            $orderData->save();

            Log::channel('city_tour')->info('City Tour API make order request success: ', [$result, $this->order->additionalData]);
        }

    }

    public function approve()
    {
        if ($this->checkOrderHasCityTourTickets()) {
            $result = $this->cityTourRepository->approveOrder($this->order);
            if (!$result || $result['status'] != 200) {
                Log::channel('city_tour')->error('City Tour API approve Error', [$result]);
                throw new RuntimeException('Невозможно оформить заказ на этот рейс.');
            } else {
                Log::channel('city_tour')->info('City Tour API approve order request success: ', [$result, $this->order->additionalData]);
            }
        }

    }


    public function checkOrderHasCityTourTickets()
    {
        $tickets = $this->order->tickets;
        foreach ($tickets as $ticket) {
            if ($ticket->provider_id == Provider::city_tour) {
                return true;
            }
        }
        return false;
    }

    public function cancel()
    {
        $result = $this->cityTourRepository->cancelOrder($this->order);
        if (!$result || $result['status'] != 200) {
            Log::channel('city_tour')->error('City Tour API cancel Error', [$result]);
            throw new RuntimeException('Не получилось сделать возврат/перенос.');
        } else {
            Log::channel('city_tour')->info('City Tour API cancel order request success: ', [$result, $this->order->additionalData]);
        }
    }

    public function delete()
    {
        if ($this->checkOrderHasCityTourTickets()) {
            $result = $this->cityTourRepository->deleteOrder($this->order);
            if (!$result || $result['status'] != 200) {
                Log::channel('city_tour')->error('City Tour API delete Error', [$result]);
                throw new RuntimeException('Невозможно удалить заказ');
            } else {
                Log::channel('city_tour')->info('City Tour API delete order request success: ', [$result, $this->order->additionalData]);
            }
        }
    }

    public function getOrderInfo()
    {
        return $this->cityTourRepository->getOrderInfo($this->order);
    }

    public function sendTickets()
    {
        if ($this->checkOrderHasCityTourTickets()) {
            return $this->cityTourRepository->sendTickets($this->order);
        }
    }
}
