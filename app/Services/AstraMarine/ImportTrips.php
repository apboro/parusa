<?php

namespace App\Services\AstraMarine;

use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Excursions\Excursion;
use App\Models\Piers\Pier;
use App\Models\Sails\Trip;
use App\Models\Ships\Menu;
use App\Models\Ships\Seats\Seat;
use App\Models\Ships\Seats\SeatCategory;
use App\Models\Ships\Seats\ShipSeatCategoryTicketGrade;
use App\Models\Ships\Ship;
use App\Models\Tickets\TicketRate;
use App\Models\Tickets\TicketsRatesList;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ImportTrips
{
    private Collection $seatCategories;
    private Collection $ships;
    private Collection $piers;

    public function __construct(private $astraApiData = new AstraMarineRepository())
    {
        $this->seatCategories = SeatCategory::all();
        $this->ticketGrades = TicketGrade::all();
        $this->ships = Ship::all();
        $this->piers = Pier::all();
    }

    public function run(): void
    {
        $trips = Trip::query()
            ->with(['additionalData'])
            ->where('start_at', '>=', now())
            ->where('provider_id', Provider::astra_marine)
            ->get();
        $excursions = Excursion::query()
            ->with('additionalData')
            ->where('provider_id', Provider::astra_marine)
            ->where('status_id', 1)
            ->get();

        if ($excursions->isEmpty())
            return;

        $astraTrips = $this->astraApiData->getEvents([
            "getTicketType" => true,
            "seatsByGroups" => true,
            "dateFrom" => now()->toIso8601String(),
            "dateTo" => now()->addDays(14)->toIso8601String()
        ]);

        foreach ($astraTrips['body']['events'] as $astraTrip) {
            $findExcursion = $excursions->first(function (Excursion $excursion) use ($astraTrip) {
                return $excursion->additionalData->provider_excursion_id === $astraTrip['serviceID'];
            });

            if ($findExcursion) {
                $findExcursion->use_seat_scheme = !$astraTrip['eventFreeSeating'];
                if ($findExcursion->isDirty('use_seat_scheme')) {
                    $findExcursion->save();
                }
                $trip = $trips->firstWhere('additionalData.provider_trip_id', $astraTrip['eventID']);
                if (!$trip) {
                    $trip = new Trip();
                    $trip->start_at = Carbon::parse($astraTrip['eventDateTime']);
                    $trip->end_at = Carbon::parse($astraTrip['eventDateTime'])->addMinutes($astraTrip['eventDuration']);
                    $trip->excursion_id = $findExcursion->id;
                    $trip->start_pier_id = $this->piers->firstWhere('external_id', $astraTrip['pierID'])->id ?? $this->importPier($astraTrip);
                    $trip->end_pier_id = $this->piers->firstWhere('external_id', $astraTrip['endPointID'])->id ?? $this->importPier($astraTrip);
                    $trip->ship_id = $this->ships->firstWhere('external_id', $astraTrip['venueID'])->id ?? $this->importShip($astraTrip);
                    $trip->cancellation_time = 60;
                    $trip->provider_id = Provider::astra_marine;
                }
                $trip->status_id = $astraTrip['availableSeats'] > 0 ? 1 : 4;
                $trip->sale_status_id = $astraTrip['availableSeats'] > 0 ? 1 : 3;
                $trip->tickets_total = $astraTrip['availableSeats'];
                $trip->save();

                $trip->additionalData()
                    ->updateOrCreate([
                        'provider_trip_id' => $astraTrip['eventID'],
                        'provider_id' => Provider::astra_marine,
                    ],
                        [
                            'with_seats' => $astraTrip['eventFreeSeating'] == false
                        ]);
                $trip->loadMissing('ship');

                if (config('app.env') == 'local' || $trip->wasRecentlyCreated) {
                    $this->importSeatCategories($astraTrip, $trip->ship_id);
                    $this->importGrades($astraTrip, $trip);
                }
            }
        }
    }

    public function importPier(array $data): int
    {
        $pier = Pier::firstOrCreate(
            ['external_id' => $data['pierID'], 'provider_id' => Provider::astra_marine],
            [
                'name' => rtrim(explode('|', $data['pierName'])[0] ?? $data['pierName']),
            ]);

        $info = $pier->info;
        $info->address = explode('|', $data['pierName'])[0] ?? $data['pierName'];
        $info->save();

        if ($pier->wasRecentlyCreated) {
            $this->piers->push($pier);
        }

        return $pier->id;
    }

    public function importShip(array $data): int
    {
        $ship = Ship::updateOrCreate(
            ['external_id' => $data['venueID'], 'provider_id' => Provider::astra_marine],
            ['name' => ucfirst($data['venueName']),
                'status_id' => 1,
                'owner' => 'Astra Marine',
                'capacity' => $data['availableSeats'],
                'ship_has_seats_scheme' => true,
                'scheme_name' => strtolower($data['venueName']) === 'meteor' ? 'astra_meteor' : strtolower($data['venueName']),
            ]);

        if ($ship->wasRecentlyCreated) {
            $this->ships->push($ship);
        }

        return $ship->id;
    }

    public function importSeatCategories(array $astraTrip, int $shipId): void
    {
        foreach ($astraTrip['seatCategories'][0] as $seatCategory) {
            $categoryDetails = $this->astraApiData->getSeatsOnEvent([
                'seatCategoryId' => $seatCategory['seatCategoryID'],
                'eventID' => $astraTrip['eventID'],
            ])['body'];

            $seatCategory = $this->seatCategories->first(function ($seatCategory) use ($categoryDetails) {
                return $seatCategory->name == $categoryDetails['seats'][0]['seatCategoryName']
                    && $seatCategory->table_seat == $categoryDetails['seats'][0]['numberOfTicketsPerSeat'] > 1
                    && $seatCategory->table_seats_quantity == $categoryDetails['seats'][0]['numberOfTicketsPerSeat']
                    && $seatCategory->provider_id == Provider::astra_marine
                    && $seatCategory->provider_category_id == $categoryDetails['seats'][0]['seatCategoryID'];
            });

            if (!$seatCategory) {
                $seatCategory = new SeatCategory();
                $seatCategory->name = $categoryDetails['seats'][0]['seatCategoryName'];
                $seatCategory->table_seat = $categoryDetails['seats'][0]['numberOfTicketsPerSeat'] > 1;
                $seatCategory->table_seats_quantity = $categoryDetails['seats'][0]['numberOfTicketsPerSeat'];
                $seatCategory->provider_id = Provider::astra_marine;
                $seatCategory->provider_category_id = $categoryDetails['seats'][0]['seatCategoryID'];
                $seatCategory->save();

                $this->seatCategories->push($seatCategory);
            }

            if (!$astraTrip['eventFreeSeating']) {
                $this->importSeats($categoryDetails['seats'], $shipId);
            }
        }
    }

    public function importSeats(array $seats, int $shipId): void
    {
        foreach ($seats as $seat) {
            Seat::updateOrCreate(
                [
                    'ship_id' => $shipId,
                    'seat_number' => $seat['aliasSeat']
                ],
                [
                    'seat_category_id' => $this->seatCategories->firstWhere('provider_category_id', $seat['seatCategoryID'])->id,
                    'provider_seat_id' => $seat['seatID'],
                ]);
        }
    }

    public function importGrades(array $astraTrip, Trip $trip): void
    {
        foreach ($astraTrip['seatCategories'][0] as $category) {
            foreach ($astraTrip['ticketTypes'][0] as $type) {
                $seatsPrices = $this->astraApiData->getSeatPrices([
                    'eventID' => $astraTrip['eventID'],
                    'seatCategoryID' => $category['seatCategoryID'],
                    'ticketTypeID' => $type['ticketTypeID'],
                    "paymentTypeID" => "000000002",
                ])['body'];

                foreach ($seatsPrices['seatPrices'] as $price) {
                    $grade = TicketGrade::updateOrCreate([
                        'name' => explode('|', $price['seatCategoryName'])[0] . ' ' . rtrim(explode('|', $price['priceTypeName'])[0]) . ' ' . $price['priceTypeDescription'],
                        'provider_id' => Provider::astra_marine,
                        'provider_ticket_type_id' => $type['ticketTypeID'],
                        'provider_category_id' => $category['seatCategoryID'],
                        'provider_price_type_id' => $price['priceTypeID'],
                        'has_menu' => $price['hasMenu'],
                    ]);

                    $this->importPrice($trip, $price, $category);

                    if (!$astraTrip['eventFreeSeating']) {
                        $this->connectShipSeatAndGrade($grade, $category['seatCategoryID'], $trip);
                    }
                    if ($price['hasMenu']) {
                        $this->importMenus($price, $trip, $grade);
                    }
                }
            }
        }
    }

    public function importPrice(Trip $trip, array $price, array $category): void
    {
        $rateList = $this->createOrUpdateRateList($trip);
        if ($price['priceTypeValueRetail'] != 0) {
            TicketRate::updateOrCreate([
                'rate_id' => $rateList->id,
                'grade_id' => TicketGrade::query()
                    ->where('provider_price_type_id', $price['priceTypeID'])
                    ->where('provider_category_id', $category['seatCategoryID'])
                    ->first()?->id
            ],
                [
                    'base_price' => $price['priceTypeValueRetail'],
                    'min_price' => $price['priceTypeValueRetail'],
                    'max_price' => $price['priceTypeValueRetail'],
                    'commission_type' => 'percents',
                    'commission_value' => 15,
                    'site_price' => $price['priceTypeValueRetail'],
                    'partner_price' => $price['priceTypeValueRetail']
                ]);
        }
    }

    public function createOrUpdateRateList($trip): TicketsRatesList
    {
        if ($ticketsRatesList = $trip->getRate()) {
            $ticketsRatesList->update(['end_at' => now()->addDays(60)->format('Y-m-d')]);
            $ticketsRatesList->save();
        } else {
            $ticketsRatesList = TicketsRatesList::create(
                [
                    'excursion_id' => $trip->excursion->id,
                    'start_at' => now()->format('Y-m-d'),
                    'end_at' => now()->addDays(60)->format('Y-m-d')
                ]);
        }
        return $ticketsRatesList;
    }

    public function connectShipSeatAndGrade(TicketGrade $grade, string $providerCategoryId, Trip $trip): void
    {
        ShipSeatCategoryTicketGrade::updateOrCreate([
            'seat_category_id' => $this->seatCategories->firstWhere('provider_category_id', $providerCategoryId)->id,
            'ticket_grade_id' => $grade->id,
            'ship_id' => $trip->ship->id,
        ]);
    }

    public function importMenus(array $price, Trip $trip, TicketGrade $grade): void
    {
        foreach ($price['menus'] as $menu) {
            $menu = Menu::updateOrCreate([
                'provider_id' => Provider::astra_marine,
                'provider_price_type_id' => $price['priceTypeID'],
                'provider_menu_id' => $menu['menuID'],
                'ship_id' => $trip->ship->id,
            ], ['name' => $menu['menuName']]);

            $menu->grades()->syncWithoutDetaching([$grade->id]);
        }
    }

}
