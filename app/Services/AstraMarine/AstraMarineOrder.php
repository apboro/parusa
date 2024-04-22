<?php

namespace App\Services\AstraMarine;

use App\Exceptions\AstraMarine\AstraMarineNoTicketException;
use App\Http\APIResponse;
use App\Models\Dictionaries\Provider;
use App\Models\Order\Order;
use App\Models\Ships\Seats\Seat;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AstraMarineOrder
{
    private Order $order;

    private Collection $tickets;

    private AstraMarineRepository $astraMarineRepository;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->tickets = $this->getTickets();
        $this->astraMarineRepository = new AstraMarineRepository();
    }

    /**
     * @throws AstraMarineNoTicketException
     */
    public function bookSeats(): void
    {
        foreach ($this->tickets as $ticket) {
            $response = $this->astraMarineRepository->bookingSeat([
                "eventID" => $ticket->trip->additionalData->provider_trip_id,
                "sessionID" => md5($this->order->phone),
                "seatID" => $ticket->seat->provider_seat_id,
                "email" => "info@parus-a.ru"
            ]);
            if (!$response['body']['isSeatBooked']) {
                throw new AstraMarineNoTicketException($response['body']['descriptionSeatBooked'] . ' место ' . $ticket->seat_number);
            }
        }
    }

    public function registerOrder(): null|JsonResponse
    {
        try {
            $orders = $this->getOrdersQueryData();
            $response = $this->astraMarineRepository->registerOrder([
                "sessionID" => md5($this->order->phone),
                "orderID" => $this->order->id,
                "paymentTypeID" => "000000002",
                "email" => "info@parus-a.ru",
                'order' => $orders,
            ]);
            if ($response['body']['isOrderRegistred']) {
                Log::channel('astra-marine')->notice('register order success: ' . json_encode($response));
                $this->saveTicketsBarcodes($response['body']);
            } else {

                return APIResponse::error('Не удалось оформить заказ:' . $response['body']['descriptionRegistredOrder']);
            }
        } catch (\Exception $exception) {
            Log::channel('astra-marine')->error($exception->getMessage() . ' ' . $exception->getFile() . ' ' . $exception->getLine());
        }

        return null;
    }

    public function getTickets(): Collection|array
    {
        return $this->order->tickets()
            ->with(['trip', 'trip.ship', 'trip.additionalData', 'seat', 'grade'])
            ->where('provider_id', Provider::astra_marine)
            ->get();
    }

    public function getOrdersQueryData(): array
    {
        foreach ($this->tickets as $ticket) {
            $orders[] = [
                "eventID" => $ticket->trip->additionalData->provider_trip_id,
                "seatID" => $ticket->seat?->provider_seat_id,
                "priceTypeID" => $ticket->grade->provider_price_type_id,
                "seatCategoryID" => $ticket->grade->provider_category_id,
                "ticketTypeID" => $ticket->grade->provider_ticket_type_id,
                "menuID" => $ticket->additionalData?->menu?->provider_menu_id,
                "quantityOfTickets" => 1,
                "resident" => ""
            ];
        }

        return $orders ?? [];
    }

    public function saveTicketsBarcodes(array $orderedTickets): void
    {
        foreach ($orderedTickets['orderedSeats'] as $orderedTicket) {
            $ticket = $this->tickets->first(function ($ticket) use ($orderedTicket) {
                return $ticket->grade->provider_ticket_type_id == $orderedTicket['ticketTypeID']
                    && $ticket->grade->provider_category_id == $orderedTicket['seatCategoryID']
                    && $ticket->grade->provider_price_type_id == $orderedTicket['priceTypeID'];
            });
            if ($ticket) {
                $this->tickets->forget($this->tickets->search($ticket));
                $ticket->additionalData()->updateOrCreate(['provider_id' => Provider::astra_marine],
                    ['provider_qr_code' => $orderedTicket['barCodes'][0]]);
            }
        }
    }

    public function confirmOrder()
    {
        try {
            $response = $this->astraMarineRepository->confirmPayment([
                'orderID' => $this->order->id,
                'orderConfirm' => true,
            ]);
            Log::channel('astra-marine')->notice('confirm order success: ' . json_encode($response));
        } catch (\Exception $exception) {
            Log::channel('astra-marine')->error('confirm order error: ' .$exception->getMessage() . ' ' . $exception->getFile() . ' ' . $exception->getLine());
        }
    }

    public function cancel()
    {
        try {
            $order_id_formatted = number_format($this->order->id, 0, '', ' ');
            $order = explode(' ', $order_id_formatted);
            $dataOrder = $order[0] . "\xC2\xA0" . $order[1];
            $response = $this->astraMarineRepository->returnOrder(["orderID" => $dataOrder]);
            Log::channel('astra-marine')->notice('cancel order success: ' . json_encode($response));
        } catch (\Exception $exception) {
            Log::channel('astra-marine')->error('cancel order error: '. $exception->getMessage() . ' ' . $exception->getFile() . ' ' . $exception->getLine());
        }
    }

    public function getOrder()
    {
        $order_id_formatted = number_format($this->order->id, 0, '', ' ');
        $order = explode(' ', $order_id_formatted);
        $dataOrder = $order[0] . "\xC2\xA0" . $order[1];
        return $this->astraMarineRepository->getOrder(["orderID" => $dataOrder]);
    }

}
