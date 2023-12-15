<?php

namespace App\Exceptions\AstraMarine;

use Exception;

class AstraMarineApiConnectException extends Exception
{
    public function __construct()
    {
        parent::__construct('Нет связи с сервером');
    }
}
