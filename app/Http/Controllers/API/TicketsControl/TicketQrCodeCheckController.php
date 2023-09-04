<?php

namespace App\Http\Controllers\API\TicketsControl;

use App\Http\APIResponse;
use App\Http\Controllers\Controller;
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
            $ticket = Ticket::with(['trip', 'trip.excursion', 'trip.startPier'])->find($ticketNumber);

            if (($signature == $expectedSignature) && $ticket) {
                $this->checkTicketStatus($ticket);
                $this->checkTripStatus($ticket);
            } else {
                return APIResponse::response('Билет не найден');
            }
        } catch (Exception $e) {
            return APIResponse::response([$ticket ?? null, $e->getMessage()]);
        }
    }

    /**
     * @throws Exception
     */
    private function checkTicketStatus(Ticket $ticket)
    {
        if (!in_array($ticket->status_id, TicketStatus::ticket_paid_statuses)){
            throw new Exception('Билет уже использован или оформлен возврат');
        }
    }

    /**
     * @throws Exception
     */
    private function checkTripStatus(Ticket $ticket)
    {
        if (in_array($ticket->trip->status_id, [3,4])){
            throw new Exception('Рейс завершен');
        }
    }
}
