<?php

namespace App\Http\Controllers\API\Order;

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

class   PartnerMakeOrderController extends ApiEditController
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

        if (($position = $current->position()) === null || ($partner = $current->partner()) === null) {
            return APIResponse::error('Оформление заказа запрещено.');
        }

        $mode = $request->input('mode');
        switch ($mode) {
            case 'reserve':
                if (!$partner->profile->can_reserve_tickets) {
                    return APIResponse::error('Ошибка. Неверное действие.');
                }
                $status_id = OrderStatus::partner_reserve;
                $successMessage = 'Бронь оформлена';
                break;
            case 'order':
                $status_id = OrderStatus::partner_paid;
                $successMessage = 'Заказ оформлен';
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
            $titles["tickets.$i.quantity"] = 'Количество';
        }

        if ($errors = $this->validate($data, $rules, $titles)) {
            return APIResponse::validationError($errors);
        }

        $totalAmount = 0;
        $tickets = [];

        foreach ($data['tickets'] as $id => $quantity) {
            if ($quantity['quantity'] > 0) {
                if (null === ($ordering = PositionOrderingTicket::query()->where(['id' => $id, 'position_id' => $position->id, 'terminal_id' => null])->first())) {
                    return APIResponse::error('Ошибка. Неверные данные билета.');
                }
                switch ($status_id) {
                    case OrderStatus::partner_reserve:
                        $ticketStatus = TicketStatus::partner_reserve;
                        break;
                    case OrderStatus::partner_paid:
                        $ticketStatus = $ordering->trip->excursion->is_single_ticket ? TicketStatus::partner_paid_single : TicketStatus::partner_paid;
                        break;
                    default:
                        return APIResponse::error('Ошибка. Неверные данные заказа.');
                }
                for ($i = 1; $i <= $quantity['quantity']; $i++) {
                    /** @var PositionOrderingTicket $ordering */
                    $ticket = new Ticket([
                        'trip_id' => $ordering->trip_id,
                        'grade_id' => $ordering->grade_id,
                        'status_id' => $ticketStatus,
                        'provider_id' => $ordering->trip->provider_id
                    ]);

                    $ticket->base_price = $ordering->getPartnerPrice() ?? $ordering->getPrice();
                    $ticket->cart_ticket_id = $ordering->id;
                    $ticket->cart_parent_ticket_id = $ordering->parent_ticket_id;
                    $ticket->backward_price = $ordering->parent_ticket_id ? $ordering->getBackwardPrice() : null;

                    $totalAmount += $ordering->parent_ticket_id !== null ? $ordering->getBackwardPrice() : $ordering->getPrice();
                    $tickets[] = $ticket;
                }
            }
        }

        if (count($tickets) === 0) {
            return APIResponse::error('Нельзя оформить заказ без билетов.');
        }

        if (($status_id === OrderStatus::partner_paid) && ($partner->account->amount - $partner->account->limit < $totalAmount)) {
            return APIResponse::error('Недостаточно средств на лицевом счёте для оформления заказа.');
        }

        try {
            DB::transaction(static function () use ($totalAmount, $status_id, $current, $partner, $tickets, $position, &$order, $data) {
                // add transaction first
                if ($status_id === OrderStatus::partner_paid) {
                    $transaction = $partner->account->attachTransaction(new AccountTransaction([
                        'type_id' => AccountTransactionType::tickets_buy,
                        'status_id' => AccountTransactionStatus::accepted,
                        'timestamp' => Carbon::now(),
                        'amount' => $totalAmount,
                        'committer_id' => $current->positionId(),
                    ]));
                }
                // create order
                $order = Order::make(
                    OrderType::partner_sale,
                    $tickets,
                    $status_id,
                    $partner->id,
                    $position->id,
                    null,
                    null,
                    $data['email'] ?? null,
                    $data['name'] ?? null,
                    $data['phone'] ?? null
                );

                NewNevaTravelOrderEvent::dispatch($order);
                NevaTravelOrderPaidEvent::dispatch($order);

                NewCityTourOrderEvent::dispatch($order);
                CityTourOrderPaidEvent::dispatch($order);

                // attach order_id to transaction
                if ($status_id === OrderStatus::partner_paid) {
                    $partner->account->updateTransaction($transaction, ['order_id' => $order->id]);
                }
                // pay commissions
                $order->payCommissions();
                // clear cart
                PositionOrderingTicket::query()->where('position_id', $position->id)->delete();
            });
        } catch (Exception $exception) {
            // remove transaction on error
            if (isset($transaction)) {
                $partner->account->detachTransaction($transaction);
            }
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success($successMessage, ['order_id' => $order->id]);
    }
}
