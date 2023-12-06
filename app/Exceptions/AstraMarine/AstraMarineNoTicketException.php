<?php

namespace App\Exceptions\AstraMarine;

use Exception;

class AstraMarineNoTicketException extends Exception
{

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
