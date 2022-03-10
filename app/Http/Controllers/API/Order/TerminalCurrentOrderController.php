<?php

namespace App\Http\Controllers\API\Order;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\LifePos\LifePosSales;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\Role;
use App\Models\Tickets\Order;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TerminalCurrentOrderController extends ApiController
{
    /**
     * Cancel order payment.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function send(Request $request): JsonResponse
    {
        $current = Currents::get($request);

        if (!$current->isStaff() || $current->terminal() === null || $current->role() === null || !$current->role()->matches(Role::terminal)) {
            return APIResponse::error('Вы не можете отправить заказ в оплату.');
        }

        try {
            $order = $this->getOrder($request);
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        if (!$order->hasStatus(OrderStatus::terminal_creating)) {
            return APIResponse::error('Нет текущего заказа.');
        }

        try {
            $order->external_id = null;
            LifePosSales::send($order, $current->terminal(), $current->position());
            $order->setStatus(OrderStatus::terminal_wait_for_pay);
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::formSuccess('Заказ отправлен в оплату.');
    }

    /**
     * Cancel order payment.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function cancel(Request $request): JsonResponse
    {
        try {
            $order = $this->getOrder($request);
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        if ($order->external_id === null || !$order->hasStatus(OrderStatus::terminal_wait_for_pay)) {
            return APIResponse::error('Нет текущего заказа или он не связан с продажей.');
        }

        try {
            LifePosSales::cancel($order);
            $order->setStatus(OrderStatus::terminal_creating);
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::formSuccess('Оплато отменена.');
    }

    /**
     * Delete order.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $order = $this->getOrder($request);
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        if (!$order->hasStatus(OrderStatus::terminal_creating)) {
            return APIResponse::error('Нельзя удалить заказ.');
        }

        $order->loadMissing('tickets');

        try {
            DB::transaction(static function () use ($order) {
                $order->tickets->map(function (Ticket $ticket) {
                    $ticket->delete();
                });
                $order->delete();
            });
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::formSuccess('Заказ удалён.');
    }

    /**
     * Get current order and check abilities.
     *
     * @param Request $request
     * @param Currents|null $currents
     *
     * @return  Order
     * @throw InvalidArgumentException
     */
    protected function getOrder(Request $request, ?Currents $current = null): Order
    {
        if ($current === null) {
            $current = Currents::get($request);
        }

        if (!$current->isStaff() || $current->terminal() === null) {
            throw new InvalidArgumentException('Неверно заданы параметры');
        }

        /** @var ?Order $order */
        $order = Order::query()->with(['tickets'])
            ->where(['position_id' => $current->positionId(), 'terminal_id' => $current->terminalId()])
            ->whereIn('status_id', OrderStatus::terminal_processing_statuses)
            ->first();

        return $order;
    }
}
