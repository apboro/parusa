<?php

namespace App\Http\Controllers\API\Order;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\Role;
use App\Models\Tickets\Order;
use App\Models\User\Helpers\Currents;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderReserveController extends ApiController
{
    /**
     * Remove reserve.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function cancel(Request $request): JsonResponse
    {
        $order = $this->getOrder($request);

        if ($order === null) {
            return APIResponse::error('Бронь не найдена');
        }

        // delete all tickets

        // delete order

        return APIResponse::formSuccess('Бронь аннулирована.');
    }

    /**
     * Remove ticket from reserve.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function remove(Request $request): JsonResponse
    {
        $order = $this->getOrder($request);

        if ($order === null) {
            return APIResponse::error('Бронь не найдена');
        }

        // delete ticket

        // delete order if no tickets

        return APIResponse::formSuccess('Билет удалён из брони.', []);
    }

    /**
     * Get requested reserve order.
     *
     * @param Request $request
     *
     * @return  Order|null
     */
    protected function getOrder(Request $request): ?Order
    {
        $current = Currents::get($request);

        $query = Order::query()
            ->with(['tickets'])
            ->whereIn('status_id', OrderStatus::order_reserved_statuses);

        if (!$current->isStaff() && $current->partnerId() !== null) {
            $query->where('partner_id', $current->partnerId());
        } else if (
            !($current->isStaff() && $current->role() && $current->role()->matches(Role::admin))
            || !($current->isStaff() && $current->role() && $current->terminalId() !== null && $current->role()->matches(Role::terminal))
        ) {
            return null;
        }

        /** @var ?Order $order */
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        /** @noinspection OneTimeUseVariablesInspection */
        $order = $query->first();

        return $order;
    }
}
