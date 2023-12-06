<?php

namespace App\Services\AstraMarine;

use App\Exceptions\AstraMarine\AstraMarineNoTicketException;
use App\Models\Order\Order;
use App\Models\Ships\Seats\Seat;
use Illuminate\Database\Eloquent\Collection;

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

    public function bookSeats()
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

    public function registerOrder()
    {
        foreach ($this->getTickets() as $ticket) {
            $order[] = [
                "eventID" => $ticket->trip->additionalData->provider_trip_id,
                "seatID" => $ticket->seat->provider_seat_id,
                "priceTypeID" => $ticket->grade->provider_price_type_id,
                "seatCategoryID" => $ticket->seat->category->provider_category_id,
                "ticketTypeID" => $ticket->grade->provider_ticket_type_id,
                "menuID" => "",
                "quantityOfTickets" => 1,
                "resident" => ""
            ];
        }

        $this->astraMarineRepository->registerOrder([
            "sessionID" => md5($this->order->phone),
            "orderID" => $this->order->id,
            "paymentTypeID" => "000000002",
            "email" => "info@parus-a.ru",
            'order' => $order ?? [],
        ]);
    }

    public function getTickets()
    {
        return $this->order->tickets()
            ->with(['trip', 'trip.ship', 'trip.additionalData', 'seat'])
            ->get();
    }

}
