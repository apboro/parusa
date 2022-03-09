<?php

namespace App\Exceptions\Tickets;

use App\Exceptions\Base\WrongStatusException;

class WrongTicketStatusException extends WrongStatusException
{
    public function __construct()
    {
        parent::__construct('Неверный статус билета');
    }
}
