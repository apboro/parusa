<?php

namespace App\Http\Controllers\API\Registries;

use App\Helpers\TicketPdf;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketRenderController extends ApiController
{
    /**
     * Download ticket.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function download(Request $request): JsonResponse
    {
        $ticket = $this->ticket($request);

        if ($ticket === null) {
            return APIResponse::error('Билет не найден');
        }

        $pdf = TicketPdf::a4($ticket);

        return APIResponse::response([
            'ticket' => base64_encode($pdf),
            'file_name' => "Билет N$ticket->id.pdf"
        ]);
    }

    /**
     * Download ticket print form.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function print(Request $request): JsonResponse
    {
        $ticket = $this->ticket($request);

        if ($ticket === null) {
            return APIResponse::error('Билет не найден');
        }

        $pdf = TicketPdf::print($ticket);

        return APIResponse::response([
            'ticket' => base64_encode($pdf),
            'file_name' => "Билет N$ticket->id.pdf"
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Ticket
     */
    protected function ticket(Request $request): ?Ticket
    {
        $current = Currents::get($request);

        return Ticket::query()
            ->where('id', $request->input('id'))
            ->whereIn('status_id', TicketStatus::ticket_printable_statuses)
            ->when(!$current->isStaff(), function (Builder $query) use ($current) {
                $query->whereHas('order', function (Builder $query) use ($current) {
                    $query->where('partner_id', $current->partnerId());
                });
            })
            ->with(['status', 'order', 'order.type', 'order.partner', 'order.position.user.profile', 'trip', 'trip.excursion', 'trip.startPier', 'grade'])
            ->first();
    }
}
