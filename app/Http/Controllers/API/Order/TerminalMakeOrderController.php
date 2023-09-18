<?php

namespace App\Http\Controllers\API\Order;

use App\Events\NewCityTourOrderEvent;
use App\Events\NewNevaTravelOrderEvent;
use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\LifePos\LifePosSales;
use App\LifePos\MockLifePos;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\Role;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Hit\Hit;
use App\Models\Order\Order;
use App\Models\Partner\Partner;
use App\Models\Positions\PositionOrderingTicket;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use App\Services\NevaTravel\NevaOrder;
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

        $partnerId = $request->input('data.partner_id');
        $partner = $partnerId ? Partner::query()->where('id', $partnerId)->first() : null;
        if ($partnerId !== null && $partner === null) {
            return APIResponse::validationError(['partner_id' => ['Партнёр не найден.']]);
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
            $titles["tickets.$i.price"] = 'Количество';
        }

        if ($errors = $this->validate($data, $rules, $titles)) {
            return APIResponse::validationError($errors);
        }

        $tickets = [];

        foreach ($data['tickets'] as $id => $ticketInfo) {
            if ($ticketInfo['quantity'] > 0) {
                if (null === ($ordering = PositionOrderingTicket::query()
                        ->where(['id' => $id, 'position_id' => $position->id, 'terminal_id' => $terminal->id])
                        ->first())
                ) {
                    return APIResponse::error('Ошибка. Неверные данные билета.');
                }
                switch ($status_id) {
                    case OrderStatus::terminal_creating:
                        $ticketStatus = TicketStatus::terminal_creating;
                        break;
                    default:
                        return APIResponse::error('Ошибка. Неверные данные заказа.');
                }
                for ($i = 1; $i <= $ticketInfo['quantity']; $i++) {
                    /** @var PositionOrderingTicket $ordering */
                    $ticket = new Ticket([
                        'trip_id' => $ordering->trip_id,
                        'grade_id' => $ordering->grade_id,
                        'status_id' => $ticketStatus,
                        'base_price' => $ordering->parent_ticket_id ? $ordering->getBackwardPrice() : $ticketInfo['price'],
                        'provider_id' => $ordering->trip->provider_id
                    ]);

                    $ticket->cart_ticket_id = $ordering->id;
                    $ticket->cart_parent_ticket_id = $ordering->parent_ticket_id;
                    $ticket->backward_price = $ordering->parent_ticket_id ? $ordering->getBackwardPrice() : null;

                    $tickets[] = $ticket;
                }
            }
        }

        if (count($tickets) === 0) {
            return APIResponse::error('Нельзя оформить заказ без билетов.');
        }

        try {
            DB::transaction(static function () use (&$order, $tickets, $status_id, $partnerId, $position, $terminal, $current, $data) {
                // create order
                $order = Order::make(
                    $partnerId === null ? OrderType::terminal : OrderType::terminal_partner,
                    $tickets,
                    $status_id,
                    $partnerId,
                    null,
                    $terminal->id,
                    $position->id,
                    $data['email'] ?? null,
                    $data['name'] ?? null,
                    $data['phone'] ?? null
                );

                // clear cart
                PositionOrderingTicket::query()->where(['position_id' => $position->id, 'terminal_id' => $terminal->id])->delete();

                NewNevaTravelOrderEvent::dispatch($order);

                NewCityTourOrderEvent::dispatch($order);

                // send order to POS
                if (app()->environment('production')) {
                    LifePosSales::send($order, $terminal, $current->position());
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
