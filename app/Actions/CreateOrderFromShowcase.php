<?php

namespace App\Actions;

use App\Exceptions\Tickets\WrongOrderException;
use App\Models\BackwardTicket;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Order\Order;
use App\Models\PromoCode\PromoCode;
use App\Models\Tickets\TicketRate;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateOrderFromShowcase
{

    public function execute(array $customerData, ?int $orderType, array $tickets, ?int $partnerId, bool $strictPrice, ?string $promocode, array $backwardTickets): Order
    {
        if (empty($tickets)) {
            throw new WrongOrderException('Невозможно создать заказ без билетов.');
        }

        $now = Carbon::now();

        /** @var int[][] $available */
        $available = [];

        // check tickets
        foreach ($tickets as $ticket) {

            // check trip, rate, site sales only option
            $trip = $ticket->trip;

            if ($orderType !== OrderType::site && $trip->isOnlySite()) {
                throw new WrongOrderException('Невозможно оформить заказ с выбранными билетами. Их можно приобрести только на сайте.');
            }

            $rateList = $trip ? $trip->getRate() : null;
            /** @var TicketRate $rate */
            $rate = $rateList ? $rateList->rates()->where('grade_id', $ticket->grade_id)->first() : null;

            if (
                $trip === null
                || ($trip->start_at < $now && $trip->excursion->is_single_ticket == 0)
                || $rate === null
                || ($rate->base_price <= 0 && $rate->grade_id !== TicketGrade::guide)
                || !$trip->hasStatus(TripStatus::regular)
                || !$trip->hasStatus(TripSaleStatus::selling, 'sale_status_id')
            ) {
                throw new WrongOrderException('Невозможно добавить один или несколько билетов в заказ.');
            }

            // check quantity
            if (!isset($available[$trip->id])) {
                $available[$trip->id] = $trip->tickets_total - $trip->tickets()->whereIn('status_id', TicketStatus::ticket_countable_statuses)->count();
            }
            if ($available[$trip->id]-- <= 0 && $trip->excursion->is_single_ticket != 1) {
                throw new WrongOrderException('Невозможно добавить один или несколько билетов в заказ. Недостаточно свободных мест на рейсе.');
            }

            // calc base price if not set
            if ($ticket->base_price === null || $ticket->backward_price !== null) {
                $ticket->base_price = $ticket->backward_price ?? $ticket->getCurrentPrice();
            } else if ($rate->partner_price === null && $strictPrice && ($ticket->base_price < $rate->min_price || $ticket->base_price > $rate->max_price)) {
                throw new WrongOrderException('Невозможно добавить один или несколько билетов в заказ. Неверно указана цена билета.');
            }
        }

        // prepare order
        $order = new Order();
        $order->setStatus(OrderStatus::showcase_creating, false);
        $order->setType($orderType, false);
        $order->partner_id = $partnerId;
        $order->position_id = null;
        $order->terminal_id = null;
        $order->terminal_position_id = null;
        $order->email = $customerData['email'];
        $order->name = $customerData['name'];
        $order->phone = $customerData['phone'];

        $promocodeId = null;

        if ($promocode) {
            $calc = \App\Helpers\Promocode::calc($promocode, $tickets, $partnerId);
            if ($calc['status'] ?? false) {
                $promocodeId = PromoCode::query()->where('code', mb_strtolower($promocode))->value('id');
            }
        }
        try {
            DB::transaction(static function () use (&$order, $tickets, $promocodeId, $backwardTickets) {
                $order->save();

                foreach ($tickets as $ticket) {
                    $ticket->order_id = $order->id;
                    $menu_id = $ticket->menu_id;

                    unset($ticket->menu_id);

                    $ticket->save();
                    if ($menu_id) {
                        $ticket->additionalData()->create([
                            'provider_id' => Provider::astra_marine,
                            'menu_id' => $menu_id
                        ]);
                    }
                    //making backward tickets for showcase
                    foreach ($backwardTickets as $index => $backwardTicket) {
                        if ($backwardTicket->grade_id === $ticket->grade_id) {
                            $backwardTicket->order_id = $order->id;
                            $backwardTicket->save();
                            $backwardTicketModel = new BackwardTicket();
                            $backwardTicketModel->order_id = $order->id;
                            $backwardTicketModel->main_ticket_id = $ticket->id;
                            $backwardTicketModel->backward_ticket_id = $backwardTicket->id;
                            $backwardTicketModel->save();
                            unset($backwardTickets[$index]);
                            break;
                        }
                    }
                }

                if ($promocodeId) {
                    $order->promocode()->sync([$promocodeId]);
                }
            });
        } catch (Exception $exception) {
            throw new WrongOrderException($exception->getMessage());
        }

        return $order;
    }

}
