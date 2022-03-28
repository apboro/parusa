<?php

namespace App\Http\Controllers\API\Order;

use App\Exceptions\Account\AccountException;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\Role;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderReturnController extends ApiController
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AccountException
     */
    public function return(Request $request): JsonResponse
    {
        $current = Currents::get($request);

        $query = Order::query()
            ->with(['tickets'])
            ->where('id', $request->input('id'))
            ->whereIn('status_id', OrderStatus::order_returnable_statuses);

        if (!$current->isStaff() && $current->partnerId() !== null) {
            $query->where('partner_id', $current->partnerId());
        } else if ($current->isStaff() && $current->role() && $current->terminalId() !== null && $current->role()->matches(Role::terminal)) {
            $query->where('terminal_id', $current->terminalId());
        } else {
            return APIResponse::error('Неверно заданы параметры');
        }

        /** @var ?Order $order */
        $order = $query->first();

        if ($order === null) {
            return APIResponse::error('Заказ не найден или не доступен для оформления возврата. ');
        }

        $ticketsToReturnIds = $request->input('tickets', []);
        $reasonOfReturn = $request->input('reason');

        if (empty($ticketsToReturnIds) || !is_array($ticketsToReturnIds)) {
            return APIResponse::error('Не указаны билеты для возврата.');
        }

        $orderTicketsIds = $order->tickets->pluck('id')->toArray();
        foreach ($ticketsToReturnIds as $ticket) {
            if (!in_array($ticket, $orderTicketsIds, true)) {
                return APIResponse::error('Ошибка. Билеты не относятся к данному заказу.');
            }
        }

        $successMessage = null;

        if (!$current->isStaff() && $current->partnerId() !== null) {
            // Partner return
            try {
                DB::transaction(static function () use ($order, $ticketsToReturnIds, $reasonOfReturn, $current) {
                    foreach ($order->tickets as $ticket) {
                        /** @var Ticket $ticket */
                        if (in_array($ticket->id, $ticketsToReturnIds, true)) {
                            if (!$ticket->hasStatus(TicketStatus::partner_paid)) {
                                throw new InvalidArgumentException('Билет имеет неверный статус для возврата.');
                            }
                            $ticket->refundTicket($current->position(), $reasonOfReturn);
                            $ticket->refundCommission($current->position());
                            $ticket->setStatus(TicketStatus::partner_returned);
                            $ticket->save();
                        }
                    }
                });
            } catch (Exception $exception) {
                return APIResponse::error($exception->getMessage());
            }
            if ($order->tickets()->whereIn('status_id', TicketStatus::ticket_countable_statuses)->count() === 0) {
                $order->setStatus(OrderStatus::partner_returned);
            } else {
                $order->setStatus(OrderStatus::partner_partial_returned);
            }
            $successMessage = 'Возврат оформлен.';

        } else if ($current->isStaff() && $current->role() && $current->terminalId() !== null && $current->role()->matches(Role::terminal)) {
            // Terminal return
            // Run lifepos process
        } else {
            return APIResponse::error('Неверно заданы параметры');
        }

        return APIResponse::success($successMessage);
    }
}
