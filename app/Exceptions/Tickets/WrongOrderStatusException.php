<?php

namespace App\Exceptions\Tickets;

use App\Exceptions\Base\WrongStatusException;

class WrongOrderStatusException extends WrongStatusException
{
    public function __construct()
    {
        parent::__construct('Неверный статус заказа');
    }
}
