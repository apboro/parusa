<?php
namespace App\Services\YagaAPI\Model;

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
}


