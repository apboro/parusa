<?php

namespace App\Exceptions\Tickets;

use Exception;

class NoTicketsForTripException extends Exception
{

    public function __construct()
    {
        parent::__construct('Нет продажи билетов на этот рейс.');
    }
}
