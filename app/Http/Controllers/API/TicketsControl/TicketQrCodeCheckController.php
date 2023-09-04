<?php

namespace App\Http\Controllers\API\TicketsControl;

use App\Http\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Tickets\Ticket;
use Exception;
use Illuminate\Http\Request;
use function Symfony\Component\String\s;

class TicketQrCodeCheckController extends Controller
{
    public function getScanData(Request $request)
    {
        try {
            $data = $request->all();
            $ticketData = explode('|', $data[0]['rawValue']);
            $ticketNumber = $ticketData[2];
            $signature = str_replace('"', '', $ticketData[3]);
            $expectedSignature = md5(config('app.key') . '|1|t|' . $ticketNumber);
            $ticket = Ticket::with(['order', 'trip', 'trip.excursion', 'trip.startPier'])->find($ticketNumber);

            if (($signature == $expectedSignature) && $ticket) {
                $ticketResource = $this->makeTicketResource($ticket);
                $this->checkTicketStatus($ticket);
                $this->checkTripStatus($ticket);
                return APIResponse::response(['ticket' => $ticketResource]);
            } else {
                return APIResponse::response(['ticket'=> null, 'notValidTicket' => 'Билет не найден']);
            }
        } catch (Exception $e) {
            return APIResponse::response(['ticket' => $ticketResource ?? null, 'notValidTicket' => $e->getMessage()]);
        }
    }

    /**
     * @throws Exception
     */
    private function checkTicketStatus(Ticket $ticket)
    {
        if (!in_array($ticket->status_id, TicketStatus::ticket_paid_statuses)) {
            throw new Exception('Билет уже использован или возвращён');
        }
    }

    /**
     * @throws Exception
     */
    private function checkTripStatus(Ticket $ticket)
    {
        if (in_array($ticket->trip->status_id, [3, 4])) {
            throw new Exception('Рейс по этому билету завершен');
        }
    }

    private function makeTicketResource(Ticket $ticket)
    {
      return [
            'order_id' => $ticket->order_id,
            'ticket_id' => $ticket->id,
            'ticket_status' => $ticket->status->name,
            'trip_id' => $ticket->trip_id,
            'excursion_name' => $ticket->trip->excursion->name,
            'trip_start_time' => $ticket->trip->start_at->format('d.m.Y H:i'),
            'customer_fio' => $ticket->order->name,
            'customer_email' => $ticket->order->email,
            'customer_phone' => $ticket->order->phone,
            'type' => $ticket->grade->name,
            'ticket_created_at' => $ticket->created_at->format('d.m.Y H:i'),
            'pier' => $ticket->trip->startPier->name,
            'order_type' => $ticket->order->type->name,
        ];
    }

    public function useTicket(Request $request)
    {
        $ticket = Ticket::with(['order', 'order.tickets'])->find($request->ticketId);
        $ticket->setStatus(TicketStatus::used);

        $orderTicketsCount = $ticket->order->tickets->count();
        $orderTicketsUsed = $ticket->order->tickets->filter(function (Ticket $ticket){
           return $ticket->hasStatus(TicketStatus::used);
        });
        if ($orderTicketsCount === $orderTicketsUsed->count()){
            $ticket->order->setStatus(OrderStatus::done);
        }

        return APIResponse::success('Билет отмечен как использованный');
    }
}
