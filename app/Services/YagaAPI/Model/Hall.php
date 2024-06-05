<?php

namespace App\Services\YagaAPI\Model;

use App\Models\Dictionaries\TicketGrade;
use App\Models\Sails\Trip;
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
            "name" => 'Причал "'. Trip::query()->activeScarletSails()->where('ship_id', $this->ship->id)->first()->startPier->name . '", теплоход '. $this->ship->name,
            "venueId" => $this->ship->id,
            "levels" => (new Level($this->grades))->getResource()
        ];
    }
}


