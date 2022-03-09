<?php

namespace App\Http\Controllers\API\Order;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Tickets\Order;
use App\Models\User\Helpers\Currents;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TerminalCurrentOrderController extends ApiController
{
    /**
     * Get current order.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function current(Request $request): JsonResponse
    {
        $current = Currents::get($request);

        if (!$current->isStaff() || $current->terminal() === null) {
            return APIResponse::error('Неверно заданы параметры');
        }

        /** @var ?Order $order */
        $order = Order::query()->with(['tickets'])
            ->where(['position_id' => $current->positionId(), 'terminal_id' => $current->terminalId()])
            ->whereIn('status_id', OrderStatus::terminal_processing_statuses)
            ->first();

        return APIResponse::response([
            'id' => $order->id ?? null,
        ]);
    }
}
