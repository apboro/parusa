<?php

namespace App\Http\APIv1\Controllers;

use App\Http\APIResponse;
use App\Http\APIv1\Requests\ApiOrderConfirmRequest;
use App\Http\APIv1\Resources\ApiOrderResource;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiOrderConfirmController extends Controller
{
    public function __invoke(ApiOrderConfirmRequest $request): Response|JsonResponse|Application|ResponseFactory
    {
        $order = Order::with(['status', 'tickets'])->where('id', $request->order_id)->first();

        if (!$order){
            return APIResponse::notFound('Заказ не найден');
        }

        if (!$order->hasStatus(OrderStatus::api_reserved)){
            return APIResponse::badRequest('Заказ не находится в резерве');
        }

        $order->setStatus(OrderStatus::api_confirmed);
        $order->tickets->each(fn (Ticket $ticket) => $ticket->setStatus(TicketStatus::api_confirmed));

        return APIResponse::response(data: ['order' => new ApiOrderResource($order)], message: 'Заказ подтвержден', unsetPayload: true);
    }

}
