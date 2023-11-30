<?php

namespace App\Exceptions\Tickets;

use Exception;

class WrongTicketsQuantityException extends Exception
{
    public function __construct()
    {
        parent::__construct('Нельзя оформить заказ без билетов.');
    }
}
