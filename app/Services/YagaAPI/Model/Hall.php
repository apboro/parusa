<?php

namespace App\Services\YagaAPI\Model;

use App\Models\Dictionaries\TicketGrade;
use App\Models\Sails\Trip;
use Illuminate\Support\Collection;

class Hall
{

    protected Collection $grades;
    protected ?Trip $trip;

    public function __construct(Collection $grades, ?Trip $trip = null)
    {
        $this->trip = $trip;
        $this->grades = $grades;
    }

    public function getResource(): array
    {
        return [
            "id" => $this->trip->start_pier_id,
            "name" => $this->trip->startPier->name,
            "venueId" => $this->trip->id,
            "levels" => (new Level($this->grades))->getResource()
        ];
    }
}


