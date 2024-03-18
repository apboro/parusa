<?php

namespace App\Http\APIv1\Controllers;


use App\Events\AstraMarineOrderPaidEvent;
use App\Events\CityTourOrderPaidEvent;
use App\Events\NevaTravelOrderPaidEvent;
use App\Helpers\PriceConverter;
use App\Http\APIResponse;
use App\Http\APIv1\Requests\ApiOrderConfirmRequest;
use App\Http\APIv1\Resources\ApiOrderResource;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountTransaction;
use App\Models\Dictionaries\AccountTransactionStatus;
use App\Models\Dictionaries\AccountTransactionType;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ApiOrderConfirmController extends Controller
{
    public function __invoke(ApiOrderConfirmRequest $request): Response|JsonResponse|Application|ResponseFactory
    {
        $order = Order::with(['status', 'tickets', 'partner'])->where('id', $request->order_id)->first();

        if (!$order){
            return APIResponse::notFound('Заказ не найден');
        }

        if (!$order->hasStatus(OrderStatus::api_reserved)){
            return APIResponse::badRequest('Заказ не находится в резерве');
        }

        try {
            DB::transaction(static function () use (&$order) {

                $order->setStatus(OrderStatus::api_confirmed);
                $order->tickets->each(fn(Ticket $ticket) => $ticket->setStatus(TicketStatus::api_confirmed));

                NevaTravelOrderPaidEvent::dispatch($order);
                CityTourOrderPaidEvent::dispatch($order);
                AstraMarineOrderPaidEvent::dispatch($order);

                $order->partner->account->attachTransaction(new AccountTransaction([
                    'type_id' => AccountTransactionType::tickets_buy,
                    'status_id' => AccountTransactionStatus::accepted,
                    'timestamp' => Carbon::now(),
                    'amount' => PriceConverter::storeToPrice($order->tickets()->sum('base_price')),
                    'order_id' => $order->id,
                ]));

                $order->payCommissions();
            });
        } catch (\Exception $e) {
            return APIResponse::error($e->getMessage());
        }

        return APIResponse::response(data: ['order' => new ApiOrderResource($order)], message: 'Заказ подтвержден', unsetPayload: true);
    }

}
