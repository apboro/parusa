<?php

namespace App\Http\Controllers\API\Registries;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class ReservesRegistry extends ApiController
{
    /**
     * Get reserves list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        $reserves = [
            'data' => [],
            'current_page' => 1,
            'last_page' => 1,
            'from' => 1,
            'to' => 20,
            'total' => 20,
            'per_page' => 20,
        ];

        for($i = 100; $i < 120; $i++) {
            $reserves['data'][] = [
                'id' => $i,
                'seller' => [],
                'tickets_count' => rand(1, 10),
                'amount' => number_format(rand(500, 2700), 2, ',', ''),
                'date_up_to' => Carbon::now()->format('d.m.Y, H:i'),
            ];
        }

        return APIResponse::paginationList($reserves);
    }


    /**
     * Get tickets list for order.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function tickets(ApiListRequest $request): JsonResponse
    {
        $tickets = [];

        for ($i = 100; $i < 105; $i++) {
            $tickets[] = [
                'id' => $i,
                'trip_date' => Carbon::now()->format('d.m.Y'),
                'trip_time' => Carbon::now()->format('H:i'),
                'excursion' => 'Медный всадник',
                'pier' => 'Адмиралтейский, 10',
                'type' => ['Взрослый', 'Детский', 'Пенсионный'][rand(0, 2)],
                'amount' => number_format(rand(100, 700), 2, ',', ''),
                'status' => 'Забронирован',
                'used' => rand(0, 1) === 1 ? Carbon::now()->addHours(-rand(10, 60))->format('d.m.Y H:i') : null,
            ];
        }

        return APIResponse::list($tickets);
    }
}
