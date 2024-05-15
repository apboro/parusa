<?php

namespace App\Http\Controllers\API\TicketsControl;

use App\Http\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\PartnerType;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use Exception;
use Illuminate\Http\Request;
use Str;
use function Symfony\Component\String\s;

class TicketQrCodeCheckController extends Controller
{
    public function getScanData(Request $request)
    {
        $data = $request->all();
        if (empty($data['manual']) && !Str::contains($data[0]['rawValue'], '1|t|')) {
            return APIResponse::response(['notValidQrCode' => 'Вы отсканировали неверный QR-код']);
        }

        if (empty($data['manual'])) {
            $ticketData = explode('|', $data[0]['rawValue']);
            $ticketNumber = $ticketData[2];
            $signature = str_replace('"', '', $ticketData[3]);
            $expectedSignature = md5(config('app.key') . '|1|t|' . $ticketNumber);
            if ($signature != $expectedSignature){
                return APIResponse::response(['notValidQrCode' => 'Билет не найден>']);
            }
        } else {
            $ticketNumber = $data['ticketNumber'];
        }

        $ticket = Ticket::with(['order', 'trip', 'trip.excursion', 'trip.startPier'])->find($ticketNumber);

        if ($ticket) {

            if (!in_array($ticket->status_id, [...TicketStatus::ticket_paid_statuses, TicketStatus::terminal_finishing])) {
                return APIResponse::response(['tickets' => [$this->makeTicketResource($ticket)], 'notValidTicket' => 'Билет уже использован или возвращен']);
            }
            if (in_array($ticket->trip->status_id, [TripStatus::finished, TripStatus::cancelled])) {
                return APIResponse::response(['tickets' => [$this->makeTicketResource($ticket)], 'notValidTicket' => 'Рейс по этому билету завершен']);
            }

            $orderTickets = $ticket
                ->order
                ->tickets
                ->whereIn('status_id', [...TicketStatus::ticket_paid_statuses, TicketStatus::terminal_finishing])
                ->transform(function (Ticket $ticket) {
                    return $this->makeTicketResource($ticket);
                })->values();

            return APIResponse::response(['tickets' => $orderTickets]);
        } else {
            return APIResponse::response(['notValidQrCode' => 'Билет не найден']);
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
            'promocode' => $ticket->order->promocode->first()?->name,
            'last_changed_at' => $ticket->updated_at->format('d.m.Y H:i'),
        ];
    }

    public function useTicket(Request $request)
    {
        $current = Currents::get($request);
        $tickets = Ticket::whereIn('id', $request->ticketIds)->get();
        foreach ($tickets as $ticket) {
            $ticket->setStatus(TicketStatus::used);
            if ($current->partner()?->type_id === PartnerType::ship_owner) {
                $ticket->additionalData()->updateOrCreate([
                    'provider_id' => Provider::scarlet_sails,
                    'shipowner_partner_id' => $current->partnerId()
                ]);
            }
        }

        return APIResponse::success('Билет отмечен как использованный');
    }
}
