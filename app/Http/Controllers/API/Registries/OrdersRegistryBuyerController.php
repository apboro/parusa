<?php

namespace App\Http\Controllers\API\Registries;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdersRegistryBuyerController extends ApiEditController
{
    /**
     * Update order buyer info.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function buyer(Request $request): JsonResponse
    {
        $data = $this->getData($request);

        if ($errors = $this->validate($data, ['email' => 'nullable|email'], ['email' => 'Email'])) {
            return APIResponse::validationError($errors);
        }

        $query = Order::query()->where('id', $request->input('id'));

        $current = Currents::get($request);
        if ($current->isRepresentative()) {
            $query->where('partner_id', $current->partnerId());
        } else if ($current->isStaffAdmin() || $current->isStaffOfficeManager() || $current->isStaffAccountant()) {
            if ($request->input('partner_id')) {
                $query->where('partner_id', $request->input('partner_id'));
            }
        } else {
            return APIResponse::error('Неверно заданы параметры');
        }
        /** @var Order|null $order */
        $order = $query->first();

        if ($order === null) {
            return APIResponse::error('Заказ не найден');
        }

        $order->name = $data['name'];
        $order->email = $data['email'];
        $order->phone = $data['phone'];
        $order->save();

        return APIResponse::success('Информация о плательщике обновлена');
    }
}
