<?php

namespace App\Services\CityTourBus;

use App\Models\AdditionalDataOrder;
use App\Models\AdditionalDataTicket;
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
            if ($result && $result['status'] == 200) {
                Log::channel('city_tour')->info('City Tour API approve order request success: ', [$result, $this->order->additionalData]);
                $this->getAndSaveTickets();
            } else {
                Log::channel('city_tour')->error('City Tour API approve Error', [$result]);
                throw new RuntimeException('Невозможно оформить заказ на этот рейс.');
            }
        }

    }

    public function getAndSaveTickets()
    {
        $providerTickets = $this->cityTourRepository->getOrderTickets($this->order)['body'];
        $orderTickets = $this->order->tickets()->with('additionalData')->get();
        foreach ($orderTickets as $orderTicket) {
            foreach ($providerTickets as $index => $providerTicket) {
                if ($providerTicket['ticket_cat_id'] === $orderTicket->grade_id) {
                    $addDataTicket = new AdditionalDataTicket();
                    $addDataTicket->ticket_id = $orderTicket->id;
                    $addDataTicket->provider_id = Provider::city_tour;
                    $addDataTicket->provider_ticket_id = $providerTicket['id'];
                    $addDataTicket->provider_qr_code = $providerTicket['qr_code'];
                    $addDataTicket->save();
                    unset($providerTickets[$index]);
                    break;
                }
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
        if ($this->checkOrderHasCityTourTickets()) {
            $result = $this->cityTourRepository->cancelOrder($this->order);
            if (!$result || $result['status'] != 200) {
                Log::channel('city_tour')->error('City Tour API cancel Error', [$result]);
                throw new RuntimeException('Не получилось сделать возврат/перенос.');
            } else {
                Log::channel('city_tour')->info('City Tour API cancel order request success: ', [$result, $this->order->additionalData]);
            }
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
}
