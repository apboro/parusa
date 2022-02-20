<?php

namespace App\Exceptions\Piers;

use App\Exceptions\Base\WrongStatusException;

class WrongPierStatusException extends WrongStatusException
{
    public function __construct()
    {
        parent::__construct('Неверный статус');
    }
}
