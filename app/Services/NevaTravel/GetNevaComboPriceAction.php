<?php

namespace App\Services\NevaTravel;

use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Excursions\Excursion;
use App\Models\Sails\Trip;
use App\Models\Tickets\TicketRate;
use Illuminate\Support\Facades\DB;

class GetNevaComboPriceAction
{

    public function run(Trip $straightTrip, Trip $backwardTrip): array
    {
        $ticketGrades = TicketGrade::where('provider_id', Provider::neva_travel)->get();
        $neva = new NevaTravelRepository();
        $response = $neva->getComboTemplatePrice($straightTrip, $backwardTrip);
        $prices = [];
        foreach ($response['body'][0] as $grade => $price) {
            if (is_iterable($price)) {
                $grade = $ticketGrades->where('external_grade_name', $grade)->first();
                if ($price['full_price'] > 0)
                    $prices[] = [
                        'grade_id' => $grade->id,
                        'name' => $grade->name,
                        'preferential' => false,
                        'base_price' => $price['full_price'] / 100 ?? null
                    ];
            }
        }

        return $prices;
    }


}
