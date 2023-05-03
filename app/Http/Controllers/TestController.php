<?php

namespace App\Http\Controllers;

use App\Models\Order\Order;
use App\Models\Ships\Ship;
use App\NevaTravel\ImportPiers;
use App\NevaTravel\ImportPrograms;
use App\NevaTravel\ImportProgramsPrices;
use App\NevaTravel\ImportShips;
use App\NevaTravel\ImportTrips;
use App\NevaTravel\NevaMakeOrder;
use App\NevaTravel\NevaTravelRepository;

class TestController extends Controller
{

    public function test()
    {
        return redirect('https://city-tours-spb.ru');
    }
    public function test1()
    {

//        $programs =json_decode($json);
//        dd($programs);
//        $nevaTravelApi = new NevaTravelRepository();
//        $piers = $nevaTravelApi->getProgramsInfo();
//        dd($piers);
//        $ships = $nevaTravelApi->getShipsInfo();
//        $programs = $nevaTravelApi->getProgramsInfo(['program_ids'=>['0f89959b-0dbc-11ed-b337-0242c0a85004']]);
//        $cruises = $nevaTravelApi->getCruisesInfo(['departure_date'=>'2023-04-28']);
//        dd(collect($cruises['body'])->filter(function($cruise){
//            return $cruise['ship_id'] == 'a773586d-0c2e-11ed-bedf-0242c0a83005';
//        }));
//        dd($programs);
//        dd($programs['body'][0]['prices_table'][0]['default_price']['full']['price']);//->default_price->full->price);
//        dd($ships);
//        (new ImportTrips())->run();
//        $meteors = Ship::whereNotNull('external_id')->pluck('external_id');
//        dd($meteors);
//        $trip = Trip::find(18);
        $params = [
            'ticket_list' => [
                [
                    'departure_point_id' => 'c42972ec-9b25-11ed-af63-0242ac1a0002',
                    'program_price_id' => 'd2b494fb-0dc4-11ed-b337-0242c0a85004',
                    'ticket_category' => 'full',
                    'purchase_price' => 100000,
                    'qty' => 1
                ]
            ],
            'hide_ticket_price' => true,
            'client_name' => 'Valera',
            'client_phone' => '126',
            'client_email' => '126@126.6',
            'comment' => 'privet'
        ];
        dd((new NevaMakeOrder())->run(Order::find(17498)));
    }
}
