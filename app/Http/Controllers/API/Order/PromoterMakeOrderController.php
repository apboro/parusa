<?php

namespace App\Http\Controllers\API\Order;

use App\Actions\CreateOrderFromPartner;
use App\Actions\CreateOrderFromPromoter;
use App\Actions\CreateTicketsFromPartner;
use App\Actions\CreateTicketsFromPromoter;
use App\Events\CityTourOrderPaidEvent;
use App\Events\NevaTravelOrderPaidEvent;
use App\Events\NewCityTourOrderEvent;
use App\Events\NewNevaTravelOrderEvent;
use App\Exceptions\Account\AccountException;
use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Account\AccountTransaction;
use App\Models\Dictionaries\AccountTransactionStatus;
use App\Models\Dictionaries\AccountTransactionType;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Hit\Hit;
use App\Models\Order\Order;
use App\Models\Positions\PositionOrderingTicket;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use App\Services\NevaTravel\NevaOrder;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class   PromoterMakeOrderController extends ApiEditController
{
    /**
     * Make order.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     *
     * @throws AccountException
     */
    public function make(Request $request): JsonResponse
    {

        Hit::register(HitSource::partner);
        $current = Currents::get($request);

        if ($current->isStaff()) {
            return APIResponse::error('Оформление заказа запрещено.');
        }

        if (($current->position()) === null || ($current->partner()) === null) {
            return APIResponse::error('Оформление заказа запрещено.');
        }

        $flat = $request->input('data');
        $data = Arr::undot($flat);

        $count = count($data['tickets'] ?? []);

        if ($count === 0) {
            return APIResponse::error('Нельзя оформить заказ без билетов.');
        }

        $rules = ['email' => 'email|required', 'phone' => 'required'];
        $titles = ['email' => 'Email', 'phone' => 'Телефон'];
        for ($i = 0; $i < $count; $i++) {
            $rules["tickets.$i.quantity"] = 'nullable|integer|min:0|bail';
            $titles["tickets.$i.quantity"] = 'Количество';
        }

        if ($errors = $this->validate($data, $rules, $titles)) {
            return APIResponse::validationError($errors);
        }

        $tickets = (new CreateTicketsFromPromoter($current))->execute($data);

        if (count($tickets['tickets']) === 0) {
            return APIResponse::error('Нельзя оформить заказ без билетов.');
        }

        try {
            DB::transaction(static function () use ($current, $tickets, &$order, $data) {
                $order = (new CreateOrderFromPromoter($current))->execute($tickets['tickets'], $data);

                PositionOrderingTicket::query()->where('position_id', $current->positionId())->delete();
            });
        } catch (Exception $exception) {

            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success('Заказ создан, ожидает оплаты.', ['order_id' => $order->id]);
    }
}
