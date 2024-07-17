<?php

namespace App\Services\YagaAPI15\Model;

use App\Helpers\PriceConverter;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Order\Order;

class OrderInfo
{

    public function __construct(private readonly Order $order)
    {
    }

    public function getResource(): array
    {
        $status = match ($this->order->status->id) {
            OrderStatus::yaga_confirmed => 'APPROVED',
            OrderStatus::yaga_reserved => 'RESERVED',
            OrderStatus::yaga_canceled => 'CANCELLED',
            default => 'UNDEFINED_ORDER_STATUS'
        };
        $orderTotal = $this->order->total();

        return [
            "attachmentType" => 'TICKETS',
            "id" => $this->order->id,
            "orderNumber" => $this->order->id,
            "status" => $status,
            "sum" => [
                "fee" => [
                    "currencyCode" => 'RUB',
                    "value" => '0'
                ],
                "price" => [
                    "currencyCode" => 'RUB',
                    "value" => PriceConverter::priceToStore($orderTotal)
                ],
                "total" => [
                    "currencyCode" => 'RUB',
                    "value" => PriceConverter::priceToStore($orderTotal)
                ]
            ],
            "tickets" => (new YagaTicket($this->order))->getResource()
        ];

    }

}
