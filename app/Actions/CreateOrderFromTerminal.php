<?php

namespace App\Actions;

use App\Exceptions\Tickets\WrongOrderException;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Order\Order;
use App\Models\Tickets\BackwardTicket;
use App\Models\Tickets\TicketRate;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateOrderFromTerminal
{
    public function __construct(private readonly Currents $current)
    {

    }

    public function execute(array $tickets, array $customerData, ?int $partnerId): Order
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

            if ($trip->isOnlySite()) {
                throw new WrongOrderException('Невозможно оформить заказ с выбранными билетами. Их можно приобрести только на сайте.');
            }

            $rateList = $trip ? $trip->getRate() : null;
            /** @var TicketRate $rate */
            $rate = $rateList?->rates()->where('grade_id', $ticket->grade_id)->first();

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
            } else if ($ticket->base_price < $rate->min_price || $ticket->base_price > $rate->max_price) {
                throw new WrongOrderException('Невозможно добавить один или несколько билетов в заказ. Неверно указана цена билета.');
            }
        }

        // prepare order
        $order = new Order();
        $order->setStatus(OrderStatus::terminal_creating, false);
        $order->setType(OrderType::terminal, false);
        $order->partner_id = $partnerId;
        $order->position_id = null;
        $order->terminal_id = $this->current->terminalId();
        $order->terminal_position_id = $this->current->positionId();
        $order->email = $customerData['email'];
        $order->name = $customerData['name'];
        $order->phone = $customerData['phone'];

        try {
            DB::transaction(static function () use (&$order, $tickets) {
                $order->save();
                $cartData = [];

                foreach ($tickets as $ticket) {

                    //collecting backward tickets for partner and terminal
                    $cart_ticket_id = $ticket->cart_ticket_id;
                    $cart_parent_ticket_id = $ticket->cart_parent_ticket_id;
                    $backward_price = $ticket->backward_price;
                    $ticket->order_id = $order->id;
                    $menu_id = $ticket->menu_id;

                    unset($ticket->cart_ticket_id, $ticket->cart_parent_ticket_id, $ticket->backward_price, $ticket->menu_id);

                    $ticket->save();
                    if ($menu_id) {
                        $ticket->additionalData()->create([
                            'provider_id' => Provider::astra_marine,
                            'menu_id' => $menu_id
                        ]);
                    }

                    $cartData[$ticket->id] = [
                        'id' => $ticket->id,
                        'cart_ticket_id' => $cart_ticket_id,
                        'cart_parent_ticket_id' => $cart_parent_ticket_id,
                        'backward_price' => $backward_price,
                    ];
                }

                //creating backward tickets for partner and terminal
                $collection = collect($cartData);
                $collection->each(function ($item) use ($collection, $order) {
                    if (!is_null($item['cart_parent_ticket_id'])) {
                        $parentItem = $collection->first(function ($parent) use ($item) {
                            return $parent['cart_ticket_id'] === $item['cart_parent_ticket_id'];
                        });

                        if ($parentItem) {
                            $backwardTicket = new BackwardTicket();
                            $backwardTicket->order_id = $order->id;
                            $backwardTicket->main_ticket_id = $parentItem['id'];
                            $backwardTicket->backward_ticket_id = $item['id'];
                            $backwardTicket->save();
                        }
                        $collection->forget($parentItem['id']);
                    }
                });

            });
        } catch (Exception $exception) {
            throw new WrongOrderException($exception->getMessage());
        }

        return $order;
    }
}
