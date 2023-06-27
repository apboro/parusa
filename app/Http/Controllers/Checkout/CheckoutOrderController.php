<?php

namespace App\Http\Controllers\Checkout;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Hit\Hit;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonException;

class CheckoutOrderController extends ApiEditController
{
    /**
     * Cancel order for checkout application.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws JsonException
     */
    public function cancel(Request $request): JsonResponse
    {
        Hit::register(HitSource::checkout);
        $secret = $request->input('secret');

        try {
            $orderSecret = json_decode(Crypt::decrypt($secret), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            return response()->json([
                'message' => 'Неверные данные заказа.',
                'error' => $exception->getMessage(),
            ], 400);
        }

        $backLink = $orderSecret['ref'] ?? null;

        /** @var Order|null $order */
        $order = Order::query()->with('status')->where('id', $orderSecret['id'])->first();

        if ($order === null) {
            return APIResponse::error('Заказ не найден.', [
                'back_link' => $backLink,
            ]);
        }

        $now = Carbon::now();
        $expires = Carbon::parse($orderSecret['ts'])->setTimezone($now->timezone)->addMinutes(env('SHOWCASE_ORDER_LIFETIME'));

        if ($expires < $now) {
            return APIResponse::success('Время, отведенное на оплату заказа истекло, заказ расформирован.', [
                'back_link' => $backLink,
            ]);
        }

        try {
            DB::transaction(static function () use ($order) {
                $order->setStatus(OrderStatus::showcase_canceled);
                $order->tickets->map(function (Ticket $ticket) {
                    $ticket->setStatus(TicketStatus::showcase_canceled);
                });
            });
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage(), [
                'back_link' => $backLink,
            ]);
        }

        Log::channel('sber_payments')->info(sprintf('Order [%s] cancelled by customer [ID:%s]', $order->id, $order->external_id));

        return APIResponse::success('Заказ расформирован.', [
            'back_link' => $backLink,
        ]);
    }
}
