<?php

namespace App\Exceptions\Trips;

use App\Exceptions\Base\WrongStatusException;

class WrongTripSaleStatusException extends WrongStatusException
{
    public function __construct()
    {
        parent::__construct('Неверный статус продаж');
    }
}
