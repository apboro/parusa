<?php

namespace App\Services\YagaAPI15\Model;

use App\Models\Excursions\Excursion;

class Organizer
{
    public function getResource(Excursion $excursion)
    {
        return $excursion->provider->name;
    }

    public static function getStaticResource(): array
    {
        return [
            "id" => 1,
            "name" => 'Алые Паруса'
        ];
    }

}


