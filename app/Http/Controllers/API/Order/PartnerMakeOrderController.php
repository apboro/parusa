<?php

namespace App\Http\Controllers\API\Order;

use App\Actions\CreateOrderFromPartner;
use App\Actions\CreateTicketsFromPartner;
use App\Events\AstraMarineNewOrderEvent;
use App\Events\AstraMarineOrderPaidEvent;
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
use App\Models\Hit\Hit;
use App\Models\Positions\PositionOrderingTicket;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rubium\RedSms\Facades\RedSms;

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
            case 'sms':
                if (!$partner->profile->can_send_sms) {
                    return APIResponse::error('Ошибка. Неверное действие.');
                }
                $status_id = OrderStatus::partner_wait_for_pay;
                $successMessage = 'Ссылка на оплату отправлена';
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

        $tickets = (new CreateTicketsFromPartner($current))->execute($data, $status_id);

        if (count($tickets['tickets']) === 0) {
            return APIResponse::error('Нельзя оформить заказ без билетов.');
        }

        if (($status_id === OrderStatus::partner_paid) && ($partner->account->amount - $partner->account->limit < $tickets['totalAmount'])) {
            return APIResponse::error('Недостаточно средств на лицевом счёте для оформления заказа.');
        }

        try {
            DB::transaction(static function () use ($status_id, $current, $partner, $tickets, $position, &$order, $data) {
                // add transaction first
                if ($status_id === OrderStatus::partner_paid) {
                    $transaction = $partner->account->attachTransaction(new AccountTransaction([
                        'type_id' => AccountTransactionType::tickets_buy,
                        'status_id' => AccountTransactionStatus::accepted,
                        'timestamp' => Carbon::now(),
                        'amount' => $tickets['totalAmount'],
                        'committer_id' => $current->positionId(),
                    ]));
                }
                $order = (new CreateOrderFromPartner($current))->execute($tickets['tickets'], $data, $status_id);

                if ($status_id === OrderStatus::partner_wait_for_pay) {
                    try {
                        $result = RedSms::send($order->phone, 'Оплатить - ' . config('app.url') . '/ext/order/payment/' . $order->hash);
                        if (!$result){
                            throw new Exception('Не удалось отправить СМС');
                        }
                    } catch (Exception $e){
                        Log::error('SMS send error:' . $e->getMessage(). ' ' . $e->getFile() . ' ' .$e->getLine());
                        throw new Exception('Не удалось отправить СМС');
                    }
                }

                NewNevaTravelOrderEvent::dispatch($order);
                NewCityTourOrderEvent::dispatch($order);
                AstraMarineNewOrderEvent::dispatch($order);

                if ($status_id === OrderStatus::partner_paid){
                    NevaTravelOrderPaidEvent::dispatch($order);
                    CityTourOrderPaidEvent::dispatch($order);
                    AstraMarineOrderPaidEvent::dispatch($order);
                }

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
            Log::error($exception);
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success($successMessage, ['order_id' => $order->id]);
    }
}
