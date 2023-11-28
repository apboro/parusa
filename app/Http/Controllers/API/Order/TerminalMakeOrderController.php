<?php

namespace App\Http\Controllers\API\Order;

use App\Actions\CreateOrderFromTerminal;
use App\Actions\CreateTicketsFromTerminal;
use App\Events\NewCityTourOrderEvent;
use App\Events\NewNevaTravelOrderEvent;
use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\LifePos\LifePosSales;
use App\LifePos\MockLifePos;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\PartnerType;
use App\Models\Dictionaries\Role;
use App\Models\Hit\Hit;
use App\Models\Order\Order;
use App\Models\Partner\Partner;
use App\Models\Positions\PositionOrderingTicket;
use App\Models\User\Helpers\Currents;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TerminalMakeOrderController extends ApiEditController
{
    /**
     * Make order.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function make(Request $request): JsonResponse
    {
        Hit::register(HitSource::terminal);
        $current = Currents::get($request);

        if (!$current->isStaff() || $current->role() === null || !$current->role()->matches(Role::terminal)) {
            return APIResponse::error('Оформление заказа запрещено.');
        }

        if (($position = $current->position()) === null || ($current->partner() !== null)) {
            return APIResponse::error('Оформление заказа запрещено.');
        }

        if (null === ($terminal = $current->terminal())) {
            return APIResponse::error('Оформление заказа запрещено.');
        }

        if (Order::query()->where(['position_id' => $position->id, 'terminal_id' => $terminal->id])
                ->whereIn('status_id', OrderStatus::terminal_processing_statuses)
                ->count() > 0
        ) {
            return APIResponse::error('Другой заказ уже находится в оформлении');
        }

        $partnerId = null;
        if ($request->input('data.without_partner') === false) {
            $partnerId = $request->input('data.partner_id');
            $partner = $partnerId ? Partner::query()->where('id', $partnerId)->first() : null;
            if ($partnerId !== null && $partner === null) {
                return APIResponse::validationError(['partner_id' => ['Партнёр не найден.']]);
            }
            if ($partner && $partner->type_id === PartnerType::promoter) {
                if ($partner->getOpenedShift() === null) {
                    return APIResponse::error('У выбранного промоутера не открыта смена');
                }
            } else {
                return APIResponse::error('Выбранный партнёр не является промоутером');
            }
        }

        $mode = $request->input('mode');

        switch ($mode) {
            case 'order':
                $status_id = OrderStatus::terminal_creating;
                $successMessage = 'Заказ отправлен в оплату.';
                break;
            default:
                return APIResponse::error('Ошибка. Неверное действие.');
        }

        $flat = $request->input('data');
        $data = Arr::undot($flat);

        $count = count($data['tickets'] ?? []);

        if ($count === 0) {
            return APIResponse::error('Нельзя оформить заказ без билетов.');
        }

        $rules = ['email' => 'email|nullable', 'phone' => 'required'];
        $titles = ['email' => 'Email', 'phone' => 'Телефон'];
        for ($i = 0; $i < $count; $i++) {
            $rules["tickets.$i.quantity"] = 'nullable|integer|min:0|bail';
            $rules["tickets.$i.price"] = 'nullable|numeric|bail';
            $titles["tickets.$i.quantity"] = 'Количество';
            $titles["tickets.$i.price"] = 'Цена';
        }

        if ($errors = $this->validate($data, $rules, $titles)) {
            return APIResponse::validationError($errors);
        }

        $tickets = (new CreateTicketsFromTerminal($current))->execute($data, $status_id);

        if (count($tickets) === 0) {
            return APIResponse::error('Нельзя оформить заказ без билетов.');
        }

        try {

            DB::transaction(static function () use (&$order, $tickets, $current, $data, $partnerId) {
                // create order

                $order = (new CreateOrderFromTerminal($current))->execute($tickets, $data, $partnerId);

                // clear cart
                PositionOrderingTicket::query()->where(['position_id' => $current->positionId(), 'terminal_id' => $current->terminalId()])->delete();

                NewNevaTravelOrderEvent::dispatch($order);

                NewCityTourOrderEvent::dispatch($order);

                // send order to POS
                if (app()->environment('production')) {
                    LifePosSales::send($order, $current->terminal(), $current->position());
                } else {
                    MockLifePos::send($order);
                }

                $order->setStatus(OrderStatus::terminal_wait_for_pay);
            });
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success($successMessage, ['order_id' => $order->id]);
    }
}
