<?php

namespace App\Http\Controllers\API\Sails;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Sails\Trip;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class TripsListController extends ApiController
{
    protected array $defaultFilters = [
        'date' => null,
        'status_id' => null,
        'excursion_id' => null,
        'start_pier_id' => null,
    ];

    protected array $rememberFilters = [
        'date',
        'status_id',
        'excursion_id',
        'start_pier_id',
    ];

    protected string $rememberKey = CookieKeys::trips_list;

    /**
     * Get staff list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        $query = Trip::query()
            ->with(['startPier', 'endPier', 'ship', 'excursion', 'status', 'saleStatus'])
            ->orderBy('start_at', 'asc');

        // apply filters
        if (!empty($filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey))) {
            if (!empty($filters['date'])) {
                $date = Carbon::parse($filters['date']);
                $query->whereDate('start_at', $date);
            }
            if (!empty($filters['status_id'])) {
                $query->where('status_id', $filters['status_id']);
            }
            if (!empty($filters['excursion_id'])) {
                $query->where('excursion_id', $filters['excursion_id']);
            }
            if (!empty($filters['start_pier_id'])) {
                $query->where('start_pier_id', $filters['start_pier_id']);
            }
        }

        // current page automatically resolved from request via `page` parameter
        $trips = $query->paginate($request->perPage(10, $this->rememberKey));

        /** @var Collection $trips */
        $trips->transform(function (Trip $trip) {
            return [
                'id' => $trip->id,
                'start_date' => $trip->start_at->format('d.m.Y'),
                'start_time' => $trip->start_at->format('H:i'),
                'excursion' => $trip->excursion->name,
                'pier' => $trip->startPier->name,
                'ship' => $trip->ship->name,
                'tickets' => [],
                'status' => $trip->status->name,
                'status_id' => $trip->status_id,
                'sale_status' => $trip->saleStatus->name,
                'sale_status_id' => $trip->sale_status_id,
            ];
        });

        return APIResponse::paginationList($trips, [
            'start_at' => 'Отправление',
            'trip_id' => '№ рейса',
            'excursion' => 'Экскурсия',
            'pier_ship' => 'Причал, теплоход',
            'tickets' => 'Осталось билетов (всего)',
            'statuses' => 'Статусы движение / продажа',
        ], [
            'filters' => $filters,
            'filters_original' => $this->defaultFilters,
        ])->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }
}
