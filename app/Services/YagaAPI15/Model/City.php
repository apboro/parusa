<?php

namespace App\Services\YagaAPI15\Model;

class City
{

    public static function getResource()
    {
        $data = [
            "cities" => [
                [
                    "additional" => (object)[],
                    "coordinates" => [
                        "latitude" => null,
                        "longitude" => null
                    ],
                    "id" => 1,
                    "integrations" => [
                        [
                            "serviceId" => null,
                            "sourceId" => null
                        ]
                    ],
                    "name" => "Санкт-Петербург",
                    "timezone" => "MSK"
                ]
            ],
            "paging" => [
                "limit" => 1,
                "offset" => 0,
                "total" => 1
            ]
        ];

        return response()->json($data);
    }

}


