<?php

namespace App\Services\NevaTravel;

use App\Models\Combo;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Excursions\Excursion;
use App\Models\Tickets\TicketRate;
use App\Services\NevaTravel\NevaTravelRepository;
use Illuminate\Support\Facades\DB;

class ImportCombos
{
    public function run()
    {
        $nevaApiData = new NevaTravelRepository();
        $combos = $nevaApiData->getCombosInfo()['body'];
        foreach ($combos as $combo) {
            Combo::updateOrCreate(
                ['combo_id' => $combo['id']],
                ['combo' => $combo]);
        }

        $this->importComboPrices();
    }

    public function importComboPrices()

    {
        $excursionsWithBack = Excursion::query()
            ->whereNotNull('reverse_excursion_id')
            ->where('provider_id', Provider::neva_travel)
            ->get();

        foreach ($excursionsWithBack as $excursion) {
            $record = DB::table('combos')
                ->whereJsonContains('combo->program_ids', $excursion->additionalData->provider_excursion_id)
                ->whereJsonContains('combo->is_active', true)
                ->first();
            $combo = json_decode($record->combo, true);
            $rates = $excursion->rateForDate(now())->rates;
            $prices = $combo['template_prices_table'][0];
            foreach ($prices as $ticket_name => $ticket_data) {
                if (is_iterable($ticket_data)) {
                    list($name, $stub) = explode('_', $ticket_name);
                    foreach ($rates as $rate) {
                        if ($name === $rate->grade->external_grade_name) {
                            foreach ($ticket_data['ticket_list'] as $ticketsPrice) {
                                if ($ticketsPrice['program_id'] === $excursion->reverseExcursion->additionalData->provider_excursion_id) {
                                    $discount = $ticketsPrice['discount']['value'];
                                    $rate->update(['backward_price_value' => $rate->base_price - $discount / 100]);
                                    $rate->save();
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
