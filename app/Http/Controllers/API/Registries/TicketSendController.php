<?php

namespace App\Http\Controllers\API\Registries;

use App\Classes\EmailReceiver;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use App\Notifications\TicketNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class TicketSendController extends ApiController
{
    /**
     * Download ticket.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function send(Request $request): JsonResponse
    {
        $ticket = $this->ticket($request);

        if ($ticket === null) {
            return APIResponse::error('Билет не найден');
        }

        if ($ticket->order->email === null) {
            return APIResponse::error('Не задан адрес получателя');
        }

        Notification::sendNow(new EmailReceiver($ticket->order->email, $ticket->order->name), new TicketNotification($ticket));

        return APIResponse::success("Билет отправлен на почту {$ticket->order->email}");
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
            ->when(!$current->isStaff(), function (Builder $query) use ($current) {
                $query->whereHas('order', function (Builder $query) use ($current) {
                    $query->where('partner_id', $current->partnerId());
                });
            })
            ->with(['status', 'order', 'order.type', 'order.partner', 'order.position.user.profile', 'trip', 'trip.excursion', 'trip.startPier', 'grade'])
            ->first();
    }
}
