<?php

namespace App\Exceptions\Tickets;

use RuntimeException;

class WrongOrderException extends RuntimeException
{
    public function __construct(?string $reason)
    {
        parent::__construct('Ошибка оформления заказа' . ($reason ? ': ' . $reason : null));
    }
}
