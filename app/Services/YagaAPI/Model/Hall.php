<?php

namespace App\Services\YagaAPI\Model;

use App\Models\Dictionaries\TicketGrade;
use App\Models\Ships\Ship;
use Illuminate\Support\Collection;

class Hall
{

    protected ?Ship $ship;
    protected Collection $grades;

    public function __construct(Collection $grades, ?Ship $ship = null)
    {
        $this->ship = $ship;
        $this->grades = $grades;
    }

    public function getResource(): array
    {
        return [
            "id" => $this->ship->id,
            "name" => $this->ship->name,
            "venueId" => $this->ship->id,
            "levels" => (new Level($this->grades))->getResource()
        ];
    }
}


