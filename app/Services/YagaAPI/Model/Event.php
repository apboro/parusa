<?php

namespace App\Services\YagaAPI\Model;


use App\Models\Excursions\Excursion;

class Event
{
    protected Excursion $excursion;

    public function __construct(Excursion $excursion)
    {
        $this->excursion = $excursion;
    }

    public function getResource(): array
    {
        return [
            [
                "ageRestriction" => '0+',
                "briefDescription" => $this->excursion->info->announce,
                "duration" => $this->excursion->info->duration,
                "fullDescription" => $this->excursion->info->description,
                "id" => $this->excursion->id,
                "images" => (new Image($this->excursion))->getResource(),
                "name" => $this->excursion->name,
                "organizer" => (new Organizer())->getResource($this->excursion),
                "permanent" => false,
                "tags" => [
                    [
                        "name" => 'Экскурсия',
                        "type" => 'тип события'
                    ]
                ],
                "type" => 'OTHER_EVENT',
            ]
        ];
    }
}



