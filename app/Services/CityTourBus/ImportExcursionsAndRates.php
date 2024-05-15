<?php /** @noinspection DuplicatedCode */

namespace App\Services\CityTourBus;


use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Excursions\Excursion;
use App\Models\Integration\AdditionalDataExcursion;
use App\Models\Piers\Pier;
use App\Models\Ships\Ship;
use App\Models\Tickets\TicketRate;
use App\Models\Tickets\TicketsRatesList;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;


class ImportExcursionsAndRates
{
    public function run()
    {

        try {
            $cityTourRepo = new CityTourRepository();
            $cityTourExcursions = $cityTourRepo->getExcursions()['body'];
            $parusaExcursions = Excursion::where('provider_id', Provider::city_tour)->get();
            foreach ($cityTourExcursions as $externalExcursion) {
                $foundExcursion = $parusaExcursions->first(function (Excursion $exc) use ($externalExcursion) {
                    return $exc->additionalData->provider_excursion_id == $externalExcursion['id'];
                });
                if (!$foundExcursion) {
                    $newExcursion = new Excursion();
                    $newExcursion->name = $externalExcursion['short_title'];
                    $newExcursion->status_id = 2;
                    $newExcursion->provider_id = Provider::city_tour;
                    $newExcursion->save();
                    $info = $newExcursion->info;
                    $info->description = strip_tags($externalExcursion['description']);
                    $info->save();

                    $additionalData = new AdditionalDataExcursion();
                    $additionalData->excursion_id = $newExcursion->id;
                    $additionalData->provider_excursion_id = $externalExcursion['id'];
                    $additionalData->provider_id = Provider::city_tour;
                    $additionalData->save();
                }

                $this->addOrUpdateGradesInDictionary($externalExcursion['tickets_categories']);

                $grades = $externalExcursion['tickets_categories'];

                $ratesList = $this->createOrUpdateRateList($foundExcursion ?? $newExcursion, $grades);

                if (!$ratesList) {
                    continue;
                }

                foreach ($grades as $grade) {
                    if ($grade['date_to'] === null || ($grade['date_to'] && Carbon::parse($grade['date_to']) > Carbon::now())) {
                        TicketRate::updateOrCreate(
                            [
                                'rate_id' => $ratesList->id,
                                'grade_id' => $grade['id'],
                            ],
                            [
                                'base_price' => $grade['price'],
                                'min_price' => $grade['price'],
                                'max_price' => $grade['price'],
                                'commission_type' => 'percents',
                                'commission_value' => 10,
                                'site_price' => $grade['price'],
                                'partner_price' => $grade['price'],
                            ]
                        );
                    }
                }
            }
            $pier = Pier::firstOrCreate([
                'external_id' => 54,
                'name' => 'Остановка автобусов City Tour',
                'provider_id' => Provider::city_tour,
            ]);
            $info = $pier->info;
            $info->address = 'Санкт-Петербург, Площадь Островского, дом 1/3';
            $info->phone = '+7 (812) 648-12-28';
            $info->save();
            Ship::firstOrCreate([
                'name' => 'Автобус City Tour',
                'enabled' => 1,
                'order' => 100,
                'status_id' => 1,
                'owner' => 'CityTour',
                'capacity' => 40,
                'label' => 'CTB',
                'provider_id' => Provider::city_tour
            ]);
        } catch (Exception $e) {
            Log::channel('city_tour')->error('import error: ' .$e->getMessage() . ' ' . $e->getFile(). ' ' . $e->getLine());
        }

    }


    public function addOrUpdateGradesInDictionary($grades)
    {
        foreach ($grades as $grade) {
            if ($grade['date_to'] === null || ($grade['date_to'] && Carbon::parse($grade['date_to']) > Carbon::now())) {
                TicketGrade::updateOrCreate(['id' => $grade['id'], 'provider_id' => Provider::city_tour],
                    [
                        'name' => $grade['category_name'],
                        'enabled' => 1,
                        'locked' => 1,
                        'external_grade_name' => $grade['category_name'],
                    ]);
            }
        }
    }

    public function createOrUpdateRateList($innerExcursion, $externalExcursionRates): ?TicketsRatesList
    {
        foreach ($externalExcursionRates as $rate) {
            if ((!$rate['date_from'] && !$rate['date_to'])
                || (!$rate['date_from'] && $rate['date_to'] && Carbon::parse($rate['date_to']) > Carbon::now()))
            {
                $ticketsRatesList = TicketsRatesList::updateOrCreate(['excursion_id' => $innerExcursion->id],
                    [
                        'end_at' => $rate['date_to'] ? Carbon::parse($rate['date_to'])->format('Y-m-d') : now()->addDays(90)->format('Y-m-d')
                    ]);
                if ($ticketsRatesList->wasRecentlyCreated) {
                    $ticketsRatesList->update(['start_at' => now()->format('Y-m-d')]);
                }
            } elseif ($rate['date_from'] && $rate['date_to'] && Carbon::parse($rate['date_to']) > Carbon::now())
            {
                $ticketsRatesList = TicketsRatesList::updateOrCreate([
                    'excursion_id' => $innerExcursion->id,
                    'start_at' => Carbon::parse($rate['date_from'])->format('Y-m-d'),
                    'end_at' => Carbon::parse($rate['date_to'])->format('Y-m-d')
                ]);
            }
    }

        return $ticketsRatesList ?? null;
    }

}
