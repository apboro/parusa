<?php

namespace App\Services\YagaAPI15\Model;

use App\Models\Excursions\Excursion;

class Image
{

    protected Excursion $excursion;

    public function __construct(Excursion $excursion)
    {
        $this->excursion = $excursion;
    }

    public function getResource(): array
    {
        return $this->excursion->images->map(function ($i) {
            return [
                'type' => 'Постер',
                'url' => $i->url,
            ];
        })->toArray();
    }

}


