<?php

namespace App\Exceptions\Tickets;

use App\Exceptions\Base\WrongDataException;

class WrongTicketCreatorException extends WrongDataException
{
    public function __construct()
    {
        parent::__construct('Ошибка. Неверные данные билета.');
    }
}
