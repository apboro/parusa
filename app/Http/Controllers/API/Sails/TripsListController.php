<?php

namespace App\Http\Controllers\API\Sails;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Sails\Trip;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use JsonException;

class TripsListController extends ApiController
{
    protected array $defaultFilters = [
        'date' => null,
        'status_id' => null,
        'excursion_id' => null,
        'start_pier_id' => null,
    ];

    protected array $rememberFilters = [
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
     *
     * @throws JsonException
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        $this->defaultFilters['date'] = Carbon::now()->format('d.m.Y');

        $query = Trip::query()
            ->with(['startPier', 'endPier', 'ship', 'excursion', 'status', 'saleStatus'])
            ->withCount(['chains', 'tickets'])
            ->orderBy('start_at', 'asc');

        // apply filters
        if (!empty($filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey))) {
            if (!empty($filters['date'])) {
                $date = Carbon::parse($filters['date']);
                $query->whereDate('start_at', $date);
            } else {
                $filters['date'] = Carbon::now()->format('d.m.Y');
                $query->whereDate('start_at', Carbon::now());
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
                'tickets_count' => $trip->getAttribute('tickets_count'),
                'tickets_total' => $trip->tickets_total,
                'status' => $trip->status->name,
                'status_id' => $trip->status_id,
                'sale_status' => $trip->saleStatus->name,
                'has_rate' => $trip->hasRate(),
                'sale_status_id' => $trip->sale_status_id,
                'chained' => $trip->getAttribute('chains_count') > 0,
            ];
        });

        return APIResponse::paginationListOld($trips, [
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
