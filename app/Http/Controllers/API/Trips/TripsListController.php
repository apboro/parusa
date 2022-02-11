<?php

namespace App\Http\Controllers\API\Trips;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Sails\Trip;
use App\Models\Sails\TripChain;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

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
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        $this->defaultFilters['date'] = Carbon::now()->format('d.m.Y');

        $query = Trip::query()
            ->with(['startPier', 'endPier', 'ship', 'excursion', 'status', 'saleStatus'])
            ->with(['chains' => function (BelongsToMany $query) {
                $query->withCount('trips');
            }])
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

        /** @var LengthAwarePaginator $trips */
        $trips->transform(function (Trip $trip) {
            /** @var TripChain $chain */
            $chain = $trip->chains->first();
            $chainStart = $chain->trips()->min('start_at');
            $chainEnd = $chain->trips()->max('start_at');

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
                'chain_trips_count' => $chain->getAttribute('trips_count'),
                'chain_trips_start_at' => $chainStart ? Carbon::parse($chainStart)->format('d.m.Y') : null,
                'chain_trips_end_at' => $chainEnd ? Carbon::parse($chainEnd)->format('d.m.Y') : null,
            ];
        });

        return APIResponse::list(
            $trips,
            ['Отправление', '№ рейса', 'Экскурсия', 'Причал, теплоход', 'Осталось билетов (всего)', 'Статусы движение / продажа'],
            $filters,
            $this->defaultFilters,
            []
        )->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }
}
