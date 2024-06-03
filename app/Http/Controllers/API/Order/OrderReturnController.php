<?php

namespace App\Http\Controllers\API\Order;

use App\Classes\EmailReceiver;
use App\Events\AstraMarineCancelOrderEvent;
use App\Events\AstraMarineNewOrderEvent;
use App\Events\AstraMarineOrderPaidEvent;
use App\Events\CityTourCancelOrderEvent;
use App\Events\CityTourOrderPaidEvent;
use App\Events\NevaTravelCancelOrderEvent;
use App\Events\NevaTravelOrderPaidEvent;
use App\Events\NewCityTourOrderEvent;
use App\Events\NewNevaTravelOrderEvent;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\LifePay\CloudPrint;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\PaymentStatus;
use App\Models\Dictionaries\Role;
use App\Models\Dictionaries\SeatStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Payments\Payment;
use App\Models\Ships\Seats\TripSeat;
use App\Models\Tickets\Ticket;
use App\Models\Tickets\TicketReturn;
use App\Models\User\Helpers\Currents;
use App\Notifications\OrderNotification;
use App\SberbankAcquiring\Connection;
use App\SberbankAcquiring\Helpers\Currency;
use App\SberbankAcquiring\HttpClient\CurlClient;
use App\SberbankAcquiring\Options;
use App\SberbankAcquiring\Sber;
use App\Services\CityTourBus\CityTourOrder;
use App\Services\NevaTravel\NevaOrder;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;
use YooKassa\Client;

class OrderReturnController extends ApiController
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function return(Request $request): JsonResponse
    {
        $current = Currents::get($request);

        $query = Order::query()
            ->with(['tickets'])
            ->where('id', $request->input('id'))
            ->whereIn('status_id', OrderStatus::order_returnable_statuses);

        if (!$current->isStaff() && $current->partnerId() !== null) {
            $query->where('partner_id', $current->partnerId());
        } else if ($current->isStaff() && $current->role() && $current->terminalId() !== null && $current->role()->matches(Role::terminal)) {
            $query->where('terminal_id', $current->terminalId());
        } else if (!$current->isStaffAdmin()) {
            return APIResponse::error('Неверно заданы параметры');
        }

        /** @var ?Order $order */
        $order = $query->first();

        if ($order === null) {
            return APIResponse::error('Заказ не найден или не доступен для оформления возврата. ');
        }

        $ticketsToReturnIds = $request->input('tickets', []);
        $reasonOfReturn = $request->input('reason');

        if (empty($ticketsToReturnIds) || !is_array($ticketsToReturnIds)) {
            return APIResponse::error('Не указаны билеты для возврата.');
        }

        $orderTicketsIds = $order->tickets->pluck('id')->toArray();
        foreach ($ticketsToReturnIds as $ticket) {
            if (!in_array($ticket, $orderTicketsIds, true)) {
                return APIResponse::error('Ошибка. Билеты не относятся к данному заказу.');
            }
        }

        $successMessage = null;

        if (!$current->isStaff() && $current->partnerId() !== null) {
            // Partner return
            try {
                DB::transaction(static function () use ($order, $ticketsToReturnIds, $reasonOfReturn, $current) {
                    foreach ($order->tickets as $ticket) {
                        /** @var Ticket $ticket */
                        if (in_array($ticket->id, $ticketsToReturnIds, true)) {
                            if (!in_array($ticket->status_id, [TicketStatus::partner_paid, TicketStatus::partner_paid_single])) {
                                throw new InvalidArgumentException('Билет имеет неверный статус для возврата.');
                            }
                            $ticket->refundTicket($current->position());
                            $ticket->refundCommission($current->position());
                            $ticket->setStatus(TicketStatus::partner_returned, false);
                            $ticket->save();
                            $ticket->return()->save(new TicketReturn(['reason' => $reasonOfReturn]));
                        }
                    }

                    foreach ($order->tickets as $ticket) {
                        if ($ticket->seat_id) {
                            TripSeat::query()
                                ->updateOrCreate(
                                    [
                                        'trip_id' => $ticket->trip_id,
                                        'seat_id' => $ticket->seat_id
                                    ],
                                    ['status_id' => SeatStatus::vacant]);
                        }
                    }

                    if ($order->tickets()->whereIn('status_id', TicketStatus::ticket_countable_statuses)->count() === 0) {
                        $order->setStatus(OrderStatus::partner_returned);
                    } else {
                        $order->setStatus(OrderStatus::partner_partial_returned);
                    }

                    NevaTravelCancelOrderEvent::dispatch($order);
                    CityTourCancelOrderEvent::dispatch($order);
                    AstraMarineCancelOrderEvent::dispatch($order);
                });
            } catch (Exception $exception) {
                Log::error('return order error ' . $exception->getMessage() . ' ' . $exception->getFile() . ' ' . $exception->getLine());
                return APIResponse::error($exception->getMessage());
            }
            $successMessage = 'Возврат оформлен.';

        } else if ($current->isStaffAdmin()) {
            // Returning tickets bought
            try {
                if (in_array($order->type_id, OrderType::types_with_sber_payment)
                    && $order->external_id === null) {
                    throw new Exception('Отсутствует внешний ID заказа');
                }
                $tickets = [];
                $returnAmount = 0;
                foreach ($order->tickets as $ticket) {
                    /** @var Ticket $ticket */
                    if (in_array($ticket->id, $ticketsToReturnIds, true)) {
                        if (!in_array($ticket->status_id, [
                            TicketStatus::showcase_paid,
                            TicketStatus::promoter_self_paid,
                            TicketStatus::showcase_paid_single,
                            TicketStatus::used,
                            TicketStatus::promoter_paid,
                            TicketStatus::partner_paid_by_link])) {
                            throw new InvalidArgumentException('Билет имеет неверный статус для возврата.');
                        }
                        $tickets[] = $ticket;
                        $returnAmount += $ticket->getPrice();
                    }
                }
                if (in_array($order->type_id, OrderType::types_with_sber_payment)) {

                    $client = new Client();
                    $client->setAuth(config('youkassa.shop_id'), config('youkassa.secret_key'));

                    $response = $client->createRefund([
                        'payment_id' => $order->external_id,
                        'amount' => [
                            'value' => $returnAmount,
                            'currency' => \YooKassa\Model\CurrencyCode::RUB,
                        ],
                    ],
                        uniqid('', true)
                    );

                    if (!$response) {
                        throw new Exception('Не удалось оформить возврат');
                    }
                }
                // change order and tickets status
                foreach ($tickets as $ticket) {
                    if ($ticket->seat_id) {
                        TripSeat::query()
                            ->updateOrCreate(
                                [
                                    'trip_id' => $ticket->trip_id,
                                    'seat_id' => $ticket->seat_id
                                ],
                                ['status_id' => SeatStatus::vacant]);
                    }
                    /** @var Ticket $ticket */
                    $ticket->refundCommission($current->position());
                    $ticket->setStatus(TicketStatus::showcase_returned, false);
                    $ticket->save();
                    $ticket->return()->save(new TicketReturn(['reason' => $reasonOfReturn]));
                }


                if ($order->tickets()->whereIn('status_id', TicketStatus::ticket_countable_statuses)->count() === 0) {
                    $order->setStatus(OrderStatus::showcase_returned);
                } else {
                    $order->setStatus(OrderStatus::showcase_partial_returned);
                }

                if (in_array($order->type_id, OrderType::types_with_sber_payment)) {
                    // add transaction
                    $payment = new Payment();
                    $payment->gate = 'sber';
                    $payment->order_id = $order->id;
                    $payment->status_id = PaymentStatus::return;
                    $payment->fiscal = '';
                    $payment->total = $returnAmount;
                    $payment->by_card = $returnAmount;
                    $payment->by_cash = 0;
                    $payment->external_id = null;
                    $payment->save();

                    CloudPrint::createReceipt($order, $tickets, CloudPrint::refund, $payment);
                }
            } catch
            (Exception $exception) {
                return APIResponse::error($exception->getMessage());
            }

            $successMessage = 'Возврат оформлен.';

            NevaTravelCancelOrderEvent::dispatch($order);
            CityTourCancelOrderEvent::dispatch($order);
            AstraMarineCancelOrderEvent::dispatch($order);

        } else if ($current->isStaff() && $current->role() && $current->terminalId() !== null && $current->role()->matches(Role::terminal)) {
            // Terminal return
            // Run lifepos process
            return APIResponse::error('Данный функционал недоступен');
        } else {
            return APIResponse::error('Неверно заданы параметры');
        }

        return APIResponse::success($successMessage);
    }
}
