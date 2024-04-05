<?php

namespace App\Actions;

use App\Exceptions\Tickets\WrongOrderException;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateOrderFromYaga
{

    public function __construct(protected array $data, protected array $tickets)
    {
    }

    public function execute(): array
    {
        $order = new Order();
        $order->setStatus(OrderStatus::yaga_reserved, false);
        $order->setType(OrderType::yaga_sale, false);
        $order->email = $this->data['customer']['email'];
        $order->name = $this->data['customer']['firstName'] . $this->data['customer']['lastName'];
        $order->phone = $this->data['customer']['phone'];

        $tickets = $this->tickets;
        try {
            DB::transaction(static function () use (&$order, $tickets) {
                $order->save();

                foreach ($tickets as $ticket) {
                    $ticket->order_id = $order->id;
                    $ticket->save();
                }
            });
        } catch (Exception $exception) {
            throw new WrongOrderException($exception->getMessage());
        }

        $orderTotal = $order->total();
        return [
            "id" => $order->id,
            "orderNumber" => $order->id,
            "status" => "reserved",
            "sum" => [
                "fee" => [
                    "currencyCode" => 'RUB',
                    "value" => '0'
                ],
                "price" => [
                    "currencyCode" => 'RUB',
                    "value" => $orderTotal
                ],
                "total" => [
                    "currencyCode" => 'RUB',
                    "value" => $orderTotal
                ]
            ],
        ];

    }
}
