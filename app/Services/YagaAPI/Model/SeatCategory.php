<?php

namespace App\Services\YagaAPI\Model;

use App\Models\Dictionaries\TicketGrade;
use App\Models\Tickets\TicketRate;

class SeatCategory
{

    public static function getResource($rates): array
    {
        /** @var TicketRate $rate */
        foreach ($rates as $rate) {
            $categories[] =
                [
                    "cost" => [
                        "fee" => [
                            "currencyCode" => 'RUB',
                            "value" => 0
                        ],
                        "price" => [
                            "currencyCode" => 'RUB',
                            "value" => $rate->partner_price
                    ],
                        "total" => [
                            "currencyCode" => 'RUB',
                            "value" => $rate->partner_price,
                        ]
                    ],
                    "id" => $rate->grade->id,
                    "name" => $rate->grade->name
                ];
        }
        return $categories ?? [];

    }
}
