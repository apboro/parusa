<?php

namespace App\Exceptions\Excursions;

use App\Exceptions\Base\WrongStatusException;

class WrongExcursionStatusException extends WrongStatusException
{
    public function __construct()
    {
        parent::__construct('Неверный статус');
    }
}
