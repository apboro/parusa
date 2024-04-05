<?php

namespace App\Services\YagaAPI\Model;

use App\Models\Order\Order;

class OrderInfo
{

    public function __construct(private Order $order)
    {
    }

    public function getResource(): array
    {
        return [

        ];

    }

}
