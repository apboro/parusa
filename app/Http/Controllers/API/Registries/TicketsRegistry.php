<?php

namespace App\Http\Controllers\API\Registries;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;

class TicketsRegistry extends ApiController
{
    /**
     * Get tickets list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        $tickets = [
            'data' => [],
            'current_page' => 1,
            'last_page' => 1,
            'from' => 1,
            'to' => 20,
            'total' => 20,
            'per_page' => 20,
        ];

        for ($i = 100; $i < 120; $i++) {
            $tickets['data'][] = [
                'date' => Carbon::now()->format('d.m.Y'),
                'time' => Carbon::now()->format('H:i'),
                'order_number' => rand(100, 700),
                'number' => $i,
                'type' => ['Взрослый', 'Детский', 'Пенсионный'][rand(0, 2)],
                'amount' => number_format(rand(100, 700), 2, ',', ''),
                'commission' => rand(0, 1) === 0 ? 'фикс.' : rand(5, 15) . '%',
                'commission_amount' => number_format(rand(10, 60), 2, ',', ''),
                'trip_date' => Carbon::now()->format('d.m.Y'),
                'trip_time' => Carbon::now()->format('H:i'),
                'trip_number' => rand(100, 1000),
                'pier' => 'Адмиралтейский, 10',
                'excursion' => 'Медный всадник',
                'sale_way' => ['Партнёрский кабинет', 'Витрина на сайте', 'QR-код'][$way = rand(0, 2)],
                'sale_by' => $way !== 2 ? 'Удалов С.А.' : '—',
                'return_up_to' => rand(0, 1) === 1 ? Carbon::now()->addHours(rand(10, 60))->format('d.m.Y H:i') : null,
            ];
        }

        return APIResponse::paginationList($tickets);
    }
}
