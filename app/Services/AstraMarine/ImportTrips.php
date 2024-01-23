<?php

namespace App\Services\AstraMarine;

use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Excursions\Excursion;
use App\Models\Menu;
use App\Models\Piers\Pier;
use App\Models\Sails\Trip;
use App\Models\Ships\Seats\Seat;
use App\Models\Ships\Seats\SeatCategory;
use App\Models\Ships\Seats\ShipSeatCategoryTicketGrade;
use App\Models\Ships\Ship;
use App\Models\Tickets\TicketRate;
use App\Models\Tickets\TicketsRatesList;
use Carbon\Carbon;
use Database\Factories\Tickets\TicketsRatesListFactory;

class ImportTrips
{

    public function __construct(private $astraApiData = new AstraMarineRepository())
    {
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
            ->where('status_id', 1)->get();

        if ($excursions->isEmpty())
            return;

        $astraTrips = $this->astraApiData->getEvents([
            "getTicketType" => true,
            "seatsByGroups" => true,
            "dateFrom" => now()->toIso8601String(),
            "dateTo" => now()->addDays(7)->toIso8601String()
        ]);

        foreach ($astraTrips['body']['events'] as $astraTrip) {
            $findExcursion = $excursions->first(function (Excursion $excursion) use ($astraTrip) {
                return $excursion->additionalData->provider_excursion_id === $astraTrip['serviceID'];
            });

            if ($findExcursion) {
                $trip = $trips->where('additionalData.provider_trip_id', $astraTrip['eventID'])->first();
                if (!$trip) {
                    $trip = new Trip();
                    $trip->start_at = Carbon::parse($astraTrip['eventDateTime']);
                    $trip->end_at = Carbon::parse($astraTrip['eventDateTime'])->addMinutes($astraTrip['eventDuration']);
                    $trip->excursion_id = $findExcursion->id;
                    $trip->start_pier_id = Pier::where('external_id', $astraTrip['pierID'])->first()->id ?? $this->importPier($astraTrip);
                    $trip->end_pier_id = Pier::where('external_id', $astraTrip['endPointID'])->first()->id ?? $this->importPier($astraTrip);
                    $trip->ship_id = Ship::where('external_id', $astraTrip['venueID'])->first()->id ?? $this->importShip($astraTrip);
                    $trip->cancellation_time = 60;
                    $trip->provider_id = Provider::astra_marine;
                }
                $trip->status_id = $astraTrip['availableSeats'] > 0 ? 1 : 4;
                $trip->sale_status_id = $astraTrip['availableSeats'] > 0 ? 1 : 3;
                $trip->tickets_total = $astraTrip['availableSeats'];
                $trip->save();

                $trip->additionalData()
                    ->firstOrCreate([
                        'provider_trip_id' => $astraTrip['eventID'],
                        'provider_id' => Provider::astra_marine]);

                $this->importSeatCategories($astraTrip, $trip->ship);
                $this->importGrades($astraTrip, $trip);
            }
        }
    }

    public
    function importPier(array $data): int
    {
        $pier = Pier::create([
            'name' => rtrim(explode('|', $data['pierName'])[0] ?? $data['pierName']),
            'external_id' => $data['pierID'],
            'provider_id' => Provider::astra_marine,
        ]);

        $info = $pier->info;
        $info->address = explode('|', $data['pierName'])[0] ?? $data['pierName'];
        $info->save();

        return $pier->id;
    }

    public
    function importShip(array $data): int
    {
        $ship = Ship::create([
            'name' => ucfirst($data['venueName']),
            'status_id' => 1,
            'owner' => 'Astra Marine',
            'capacity' => $data['availableSeats'],
            'external_id' => $data['venueID'],
            'provider_id' => Provider::astra_marine,
            'ship_has_seats_scheme' => $data['eventFreeSeating'] == false,
        ]);

        return $ship->id;
    }

    public function importSeatCategories(array $astraTrip, Ship $ship): void
    {
        foreach ($astraTrip['seatCategories'][0] as $seatCategory) {
            $categoryDetails = $this->astraApiData->getSeatsOnEvent([
                'seatCategoryId' => $seatCategory['seatCategoryID'],
                'eventID' => $astraTrip['eventID'],
            ])['body'];

            SeatCategory::updateOrCreate([
                'name' => $categoryDetails['seats'][0]['seatCategoryName'],
                'table_seat' => $categoryDetails['seats'][0]['numberOfTicketsPerSeat'] > 1,
                'table_seats_quantity' => $categoryDetails['seats'][0]['numberOfTicketsPerSeat'],
                'provider_id' => Provider::astra_marine,
                'provider_category_id' => $categoryDetails['seats'][0]['seatCategoryID'],
            ]);
            $this->importSeats($categoryDetails['seats'], $ship);
        }
    }

    public function importSeats(array $seats, Ship $ship): void
    {
        foreach ($seats as $seat) {
            Seat::updateOrCreate([
                'ship_id' => $ship->id,
                'seat_number' => $seat['aliasSeat']],

                ['seat_category_id' => SeatCategory::where('provider_category_id', $seat['seatCategoryID'])->first()?->id,
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
                    "paymentTypeID" => "000000001",
                ])['body'];

                foreach ($seatsPrices['seatPrices'] as $price) {
                    $grade = TicketGrade::updateOrCreate([
                        'name' => explode('|', $price['seatCategoryName'])[0] . ' ' . rtrim(explode('|', $price['priceTypeName'])[0]),
                        'provider_id' => Provider::astra_marine,
                        'provider_ticket_type_id' => $type['ticketTypeID'],
                        'provider_category_id' => $category['seatCategoryID'],
                        'provider_price_type_id' => $price['priceTypeID'],
                        'has_menu' => $price['hasMenu'],
                    ]);
                    $this->importPrice($trip, $price, $category);
                    $this->connectShipSeatAndGrade($grade, $category['seatCategoryID'], $trip);
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
        TicketRate::updateOrCreate([
            'rate_id' => $rateList->id,
            'grade_id' => TicketGrade::where('provider_price_type_id', $price['priceTypeID'])
                ->where('provider_category_id', $category['seatCategoryID'])
                ->first()->id
        ],
            [
                'base_price' => $price['priceTypeValueBoxOffice'],
                'min_price' => $price['priceTypeValueBoxOffice'],
                'max_price' => $price['priceTypeValueBoxOffice'],
                'commission_type' => 'percents',
                'commission_value' => 10,
                'site_price' => $price['priceTypeValueBoxOffice'],
                'partner_price' => $price['priceTypeValueBoxOffice']
            ]);
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
            'seat_category_id' => SeatCategory::where('provider_category_id', $providerCategoryId)->first()?->id,
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
            ],['name' => $menu['menuName']]);

            $menu->grades()->syncWithoutDetaching([$grade->id]);
        }
    }

}
