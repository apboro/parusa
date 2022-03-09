<?php

namespace App\Exceptions\Tickets;

use App\Exceptions\Base\WrongTypeException;

class WrongOrderTypeException extends WrongTypeException
{
    public function __construct()
    {
        parent::__construct('Неверный тип заказа');
    }
}
