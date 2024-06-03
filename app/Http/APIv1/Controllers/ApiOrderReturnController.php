<?php

namespace App\Http\APIv1\Controllers;


use App\Exceptions\Account\AccountException;
use App\Http\APIResponse;
use App\Http\APIv1\Requests\ApiOrderReturnRequest;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\SeatStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Order\Order;
use App\Models\Ships\Seats\TripSeat;
use App\Models\Tickets\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ApiOrderReturnController extends Controller
{
    public function __invoke(ApiOrderReturnRequest $request): \Illuminate\Http\JsonResponse
    {
        $order = Order::with('tickets')->where('id', $request->order_id)->first();

        if (!$order) {
            return APIResponse::notFound('Заказ не найден');
        }

        if (!$order->hasStatus(OrderStatus::api_confirmed)) {
            return ApiResponse::badRequest('Неподтвержденный заказ нельзя отменить');
        }

        $trip = $order->tickets[0]->trip;
        if ($trip->hasStatus(TripStatus::processing) || $trip->hasStatus(TripStatus::finished)) {
            return ApiResponse::badRequest('Нельзя отменить заказ на ушедший рейс');
        }

        try {
            DB::transaction(static function () use ($order) {
                $order->setStatus(OrderStatus::api_returned);
                $order->tickets->each(/**
                 * @throws AccountException
                 */ function (Ticket $ticket) {
                    $ticket->refundTicket();
                    $ticket->refundCommission();
                    $ticket->setStatus(TicketStatus::api_returned);
                    if ($ticket->seat) {
                        TripSeat::query()
                            ->updateOrCreate(['trip_id' => $ticket->trip->id, 'seat_id' => $ticket->seat->id],
                                ['status_id' => SeatStatus::vacant]);
                    }
                });
            });
        } catch (\Exception $e) {
            Log::channel('apiv1')->error($e->getMessage(). ' ' . $e->getFile(). ' ' . $e->getLine());
            return APIResponse::error($e->getMessage());
        }

        return APIResponse::success('Заказ отменен', unsetPayload: true);
    }

}
