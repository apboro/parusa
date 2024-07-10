<?php

namespace App\Actions;

use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Excursions\Excursion;
use App\Models\Sails\Trip;
use App\Models\Tickets\TicketRate;
use App\Services\NevaTravel\NevaTravelRepository;
use Illuminate\Support\Facades\DB;

class GetNevaComboPriceAction
{

    public function run(Trip $trip): array
    {
        $programId = $trip->excursion->additionalData->provider_excursion_id;
        $record = DB::table('combos')->whereJsonContains('combo->program_ids', $programId)->whereJsonContains('combo->is_active', true)->get();
        if ($record->isEmpty()){
            return [];
        }
        $combo = json_decode($record[0]->combo, true);
        $rates = (new GetNevaTripPriceAction())->run($trip);
        $prices = $combo['template_prices_table'][0];
        $struct = [];
        foreach ($prices as $ticket_name => $ticket_data) {
            if (is_iterable($ticket_data)) {
                foreach ($rates as $rate) {
                    list($name, $stub) = explode('_', $ticket_name);
                    if ($name === $rate['external_grade_name']) {
                        $discount = $ticket_data['ticket_list'][0]['discount']['value'] == 0 ? $ticket_data['ticket_list'][1]['discount']['value'] : $ticket_data['ticket_list'][0]['discount']['value'];
                        $struct[] = [
                            'grade_id' => $rate['id'],
                            'price' => $rate['value'] * 100 - $discount
                        ];

                        //update excursion rates for backward ticket
                        Excursion::where('reverse_excursion_id', $trip->excursion_id)->first()?->rateForDate(now())?->rates->each(function (TicketRate $innerRate) use ($rate, $programId, $discount) {
                            if ($innerRate->grade_id === $rate['id']){
                                if ($innerRate->backward_price_value != $rate['value'] - $discount / 100) {
                                    $innerRate->update(['backward_price_value' => $rate['value'] - $discount / 100]);
                                    $innerRate->save();
                                }
                            }
                        });
                    }
                }
            }
        }
        return $struct;
    }


}
