<?php

namespace App\Http\Controllers\API\Registries;

use App\Classes\EmailReceiver;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\User\Helpers\Currents;
use App\Notifications\OrderNotification;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OrderSendController extends ApiController
{
    /**
     * Send order.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function send(Request $request): JsonResponse
    {
        $order = $this->order($request);

        if ($order === null) {
            return APIResponse::error('Заказ не найден');
        }

        if ($order->email === null) {
            return APIResponse::error('Не задан адрес получателя');
        }

        try {
            Notification::sendNow(new EmailReceiver($order->email, $order->name), new OrderNotification($order));
        } catch (Exception $exception) {
            Log::channel('tickets_sending')->error(sprintf("Error order [%s] sending tickets [%s]: %s", $order->id, $order->email, $exception->getMessage()));
            return APIResponse::error("Ошибка отправки билетов: " . $exception->getMessage());
        }

        return APIResponse::success("Заказ отправлен на почту {$order->email}");
    }

    /**
     * @param Request $request
     *
     * @return Order
     */
    protected function order(Request $request): ?Order
    {
        $current = Currents::get($request);

        return Order::query()
            ->where('id', $request->input('id'))
            ->whereIn('status_id', OrderStatus::order_printable_statuses)
            ->whereHas('tickets', function (Builder $query) {
                $query->whereIn('status_id', TicketStatus::ticket_printable_statuses);
            })
            ->when(!$current->isStaff(), function (Builder $query) use ($current) {
                $query->where('partner_id', $current->partnerId());
            })
            ->with(['status', 'type', 'partner', 'position.user.profile'])
            ->first();
    }
}
