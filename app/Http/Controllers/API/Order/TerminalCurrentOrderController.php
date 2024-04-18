<?php

namespace App\Http\Controllers\API\Order;

use App\Events\AstraMarineOrderPaidEvent;
use App\Events\CityTourOrderPaidEvent;
use App\Events\NevaTravelOrderPaidEvent;
use App\Helpers\Fiscal;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\LifePos\LifePosSales;
use App\LifePos\MockLifePos;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\PaymentStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Hit\Hit;
use App\Models\Order\Order;
use App\Models\Payments\Payment;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        Hit::register(HitSource::terminal);
        $current = Currents::get($request);

        if (!$current->isStaffTerminal()) {
            return APIResponse::error('Вы не можете отправить заказ в оплату.');
        }

        try {
            $order = $this->getOrder($request);
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        if (!$order->hasStatus(OrderStatus::terminal_creating) && !$order->hasStatus(OrderStatus::terminal_creating_from_reserve)) {
            return APIResponse::error('Невозможно отправить текущий заказ в оплату.');
        }

        try {
            LifePosSales::send($order, $current->terminal(), $current->position());
            $onTerminal = $order->hasStatus(OrderStatus::terminal_creating);
            DB::transaction(static function () use ($order, $onTerminal) {
                foreach ($order->tickets as $ticket) {
                    /** @var Ticket $ticket */
                    $ticket->setStatus($onTerminal ? TicketStatus::terminal_wait_for_pay : TicketStatus::terminal_wait_for_pay_from_reserve);
                }
                $order->setStatus($onTerminal ? OrderStatus::terminal_wait_for_pay : OrderStatus::terminal_wait_for_pay_from_reserve);
            });
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success('Заказ отправлен в оплату.');
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
        Hit::register(HitSource::terminal);
        try {
            $order = $this->getOrder($request);
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        if (!$order->hasStatus(OrderStatus::terminal_wait_for_pay) && !$order->hasStatus(OrderStatus::terminal_wait_for_pay_from_reserve)) {
            return APIResponse::error('Невозможно. Текущий заказ не ожидает оплаты.');
        }

        try {
            if ($order->external_id !== null) {
                LifePosSales::cancel($order);
            }
            $onTerminal = $order->hasStatus(OrderStatus::terminal_wait_for_pay);
            DB::transaction(static function () use ($order, $onTerminal) {
                foreach ($order->tickets as $ticket) {
                    /** @var Ticket $ticket */
                    $ticket->setStatus($onTerminal ? TicketStatus::terminal_creating : TicketStatus::terminal_creating_from_reserve);
                }
                $order->setStatus($onTerminal ? OrderStatus::terminal_creating : OrderStatus::terminal_creating_from_reserve);
            });
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success('Оплата отменена.');
    }

    /**
     * Save unconfirmed order payment.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function saveUnconfirmed(Request $request): JsonResponse
    {
        Hit::register(HitSource::terminal);
        try {
            $order = $this->getOrder($request);
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        if (!$order->hasStatus(OrderStatus::terminal_wait_for_pay) && !$order->hasStatus(OrderStatus::terminal_wait_for_pay_from_reserve)) {
            return APIResponse::error('Невозможно. Текущий заказ не ожидает оплаты.');
        }

        try {
            DB::transaction(static function () use ($order) {
                $order->payment_unconfirmed = true;
                $order->setStatus(OrderStatus::terminal_finishing);
                $order->tickets->map(function (Ticket $ticket) {
                    $ticket->setStatus(TicketStatus::terminal_finishing);
                });
            });

        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success('Заказ сохранен без подтверждения платежа');
    }

    /**
     * Finish order.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function close(Request $request): JsonResponse
    {
        Hit::register(HitSource::terminal);
        try {
            $order = $this->getOrder($request);
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        if ($order->external_id === null || !$order->hasStatus(OrderStatus::terminal_finishing)) {
            return APIResponse::error('Нет текущего заказа или он не связан с продажей.');
        }

        try {
            $order->loadMissing('tickets');

            DB::transaction(static function () use ($order) {
                foreach ($order->tickets as $ticket) {
                    /** @var Ticket $ticket */
                    $ticket->setStatus(TicketStatus::terminal_paid);
                }
                $order->setStatus(OrderStatus::terminal_paid);
            });

            $order->payCommissions();

        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success('Заказ закрыт.');
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
        Hit::register(HitSource::terminal);
        try {
            $order = $this->getOrder($request);
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        if (!$order->hasStatus(OrderStatus::terminal_creating) && !$order->hasStatus(OrderStatus::terminal_creating_from_reserve)) {
            return APIResponse::error('Нельзя удалить заказ.');
        }

        $order->loadMissing('tickets');

        $fromReserve = $order->hasStatus(OrderStatus::terminal_creating_from_reserve);

        try {
            DB::transaction(static function () use ($order, $fromReserve) {
                $order->tickets->map(function (Ticket $ticket) use ($fromReserve) {
                    $ticket->setStatus($fromReserve ? OrderStatus::partner_reserve : OrderStatus::terminal_canceled);
                });

                $order->setStatus($fromReserve ? OrderStatus::partner_reserve : OrderStatus::terminal_canceled);
            });

        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success('Заказ расформирован.');
    }

    /**
     * Get current order status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function status(Request $request): JsonResponse
    {
        Hit::register(HitSource::terminal);
        $current = Currents::get($request);

        try {
            $order = $this->getOrder($request, $current);
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        try {
            if ($order->external_id) {
                if (app()->environment('production')) {
                    $payments = LifePosSales::getSalePayments($order->external_id);
                } else {
                    $payments = MockLifePos::getSalePayments($order->external_id);
                }
                if (isset($payments['items']) && count($payments['items']) > 0) {
                    foreach ($payments['items'] as $receivedPayment) {
                        if (!isset($receivedPayment['type_of']) || $receivedPayment['type_of'] !== 'SalePayment') {
                            continue;
                        }

                        // add payment
                        // search existing payment to update
                        if (!empty($receivedPayment['guid'])) {
                            /** @var Payment|null $payment */
                            $payment = Payment::query()->where('external_id', $receivedPayment['guid'])->first();
                        }
                        // if no payment to update create new
                        if (!isset($payment)) {
                            $payment = new Payment();
                        }

                        $payment->gate = 'lifepos';
                        $payment->status_id = PaymentStatus::sale;
                        $payment->order_id = $order->id ?? null;
                        $payment->fiscal = $receivedPayment['fiscal_document']['guid'] ?? null;
                        $payment->total = ($receivedPayment['total_sum']['value'] ?? 0) / 100;
                        $payment->by_card = ($receivedPayment['sum_by_card']['value'] ?? 0) / 100;
                        $payment->by_cash = ($receivedPayment['sum_by_cash']['value'] ?? 0) / 100;
                        $payment->external_id = $receivedPayment['guid'];
                        $payment->terminal_id = $current->terminalId();
                        $payment->position_id = $current->positionId();
                        $payment->save();


                        if ($receivedPayment['fiscal_document']['guid']) {
                            $receipt = LifePosSales::getFiscal($receivedPayment['fiscal_document']['guid']);
                            if (isset($receipt['sources']['print_view'])) {
                                $print = $receipt['sources']['print_view'];
                                Fiscal::put('lifepos', $receipt['guid'], $print);
                            }
                        }

                        // set order status
                        $order->payment_unconfirmed = false;
                        $order->setStatus(OrderStatus::terminal_finishing);
                        $order->tickets->map(function (Ticket $ticket) {
                            $ticket->setStatus(TicketStatus::terminal_finishing);
                        });

                        NevaTravelOrderPaidEvent::dispatch($order);
                        CityTourOrderPaidEvent::dispatch($order);
                        AstraMarineOrderPaidEvent::dispatch($order);
                    }
                }
            }
        } catch (Exception $exception) {
            Log::channel('lifepos_payments')->error('error in TerminalCurrentOrderController', [$exception, $order, $payments ?? null]);
        }

        return APIResponse::response([
            'waiting_for_pay' => $order->hasStatus(OrderStatus::terminal_wait_for_pay) || $order->hasStatus(OrderStatus::terminal_wait_for_pay_from_reserve),
        ]);
    }

    /**
     * Get current order and check abilities.
     *
     * @param Request $request
     * @param Currents|null $current
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
            ->where(['terminal_position_id' => $current->positionId(), 'terminal_id' => $current->terminalId()])
            ->whereIn('status_id', OrderStatus::terminal_processing_statuses)
            ->first();

        if ($order === null) {
            throw new InvalidArgumentException('Нет заказа в оформлении.');
        }

        return $order;
    }
}
