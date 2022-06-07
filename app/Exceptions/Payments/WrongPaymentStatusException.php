<?php

namespace App\Exceptions\Payments;

use App\Exceptions\Base\WrongStatusException;

class WrongPaymentStatusException extends WrongStatusException
{
    public function __construct()
    {
        parent::__construct('Неверный статус');
    }
}
