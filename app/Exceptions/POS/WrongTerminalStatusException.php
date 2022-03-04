<?php

namespace App\Exceptions\POS;

use App\Exceptions\Base\WrongStatusException;

class WrongTerminalStatusException extends WrongStatusException
{
    public function __construct()
    {
        parent::__construct('Неверный статус');
    }
}
