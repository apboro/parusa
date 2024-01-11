<?php

namespace App\Http\APIv1\Controllers;

use App\Http\APIResponse;
use App\Http\APIv1\Requests\ApiOrderReturnRequest;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;

class ApiOrderReturnController extends Controller
{
    public function __invoke(ApiOrderReturnRequest $request)
    {
        $order = Order::where('id', $request->order_id)->first();

        if (!$order) {
            return APIResponse::notFound('Заказ не найден');
        }

        if (!$order->hasStatus(OrderStatus::api_confirmed)){
            return ApiResponse::badRequest('Неподтвержденный заказ нельзя отменить');
        }

        $order->setStatus(OrderStatus::api_returned);
        $order->tickets->each(fn (Ticket $ticket) => $ticket->setStatus(TicketStatus::api_returned));

        return APIResponse::success('Заказ отменен', unsetPayload: true);
    }

}
