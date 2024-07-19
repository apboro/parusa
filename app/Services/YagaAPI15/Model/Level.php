<?php

namespace App\Services\YagaAPI15\Model;

use Illuminate\Support\Collection;

class Level
{
    protected Collection $grades;

    public function __construct(Collection $grades)
    {
        $this->grades = $grades;
    }

    public function getResource(): array
    {
        foreach ($this->grades as $grade) {
            $levels[] = [
                "admission" => true,
                "entranceId" => '',
                "entranceName" => '',
                "id" => $grade->id,
                "name" => $grade->name,
            ];
        }

        return $levels ?? [];
    }

    public function getResourceWithSeatsCount($ticketsCount): array
    {
        foreach ($this->grades as $grade) {
            $level =
                [
                    "admission" => true,
                    "admissionStates" => AdmissionState::getResource($this->grades, $ticketsCount),
                    "id" => $grade->id,
                    "name" => $grade->name,
                ];

            $levels[] = $level;
        }

        return $levels ?? [];
    }
}


