<?php

namespace App\Services\NevaTravel;

use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Sails\Trip;

class GetNevaTripPriceAction
{

    public function run(Trip $trip, bool $forShowcase = false): array
    {
        $ticketGrades = TicketGrade::where('provider_id', Provider::neva_travel)->get();
        $neva = new NevaTravelRepository();
        $response = $neva->getCruisesInfo(['point_id' => $trip->additionalData->provider_trip_id]);
        $prices = [];
        foreach ($response['body'][0]['default_arrival']['prices_table'][0] as $grade => $price) {
            if (is_iterable($price)) {
                $grade = $ticketGrades->where('external_grade_name', $grade)->first();
                if ($price['full_price'] > 0)
                    if ($forShowcase) {
                        $prices[] = [
                            'grade_id' => $grade->id,
                            'name' => $grade->name,
                            'preferential' => false,
                            'base_price' => $price['full_price'] / 100 ?? null
                        ];
                    } else {
                        $prices[] = [
                            'id' => $grade->id,
                            'name' => $grade->name,
                            'preferential' => false,
                            'value' => $price['full_price'] / 100 ?? null,
                            'external_grade_name' => $grade->external_grade_name,
                        ];
                    }
            }
        }

        return $prices;
    }



}
