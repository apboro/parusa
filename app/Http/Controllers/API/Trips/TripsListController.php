<?php

namespace App\Http\Controllers\API\Trips;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Http\Resources\StopResource;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Excursions\Excursion;
use App\Models\Hit\Hit;
use App\Models\Piers\Pier;
use App\Models\Sails\Trip;
use App\Models\Sails\TripChain;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class TripsListController extends ApiController
{
    protected array $defaultFilters = [
        'date' => null,
        'status_id' => null,
        'provider_id' => null,
        'excursion_id' => null,
        'start_pier_id' => null,
        'excursion_type_id' => null
    ];

    protected array $rememberFilters = [
        'status_id',
        'provider_id',
        'excursion_id',
        'start_pier_id',
        'excursion_type_id',
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
        Hit::register(HitSource::admin);
        $this->defaultFilters['date'] = Carbon::now()->format('Y-m-d');
        $startPierId = $request->input('start_pier_id');
        $excursionId = $request->input('excursion_id');
        $excursionTypeId = $request->input('filters.excursion_type_id');

        $query = Trip::query()
            ->with(['startPier', 'endPier', 'ship', 'excursion', 'status', 'saleStatus', 'provider'])
            ->where('excursions.status_id', 1)
            ->whereHas('provider', function ($provider) {
                $provider->where('enabled', true);
            })
            ->with(['chains' => function (BelongsToMany $query) {
                $query->withCount('trips');
            }])
            ->withCount(['chains', 'tickets' => function (Builder $query) {
                $query->whereIn('status_id', TicketStatus::ticket_countable_statuses);
            }])
            ->join('excursions', 'excursions.id', '=', 'trips.excursion_id')
            ->groupByRaw("case when `is_single_ticket`=1 THEN excursion_id else trips.id end")
            ->orderBy('is_single_ticket', 'desc')
            ->orderBy('trips.start_at');

        // apply filters
        if (!empty($filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey))) {
            if (!empty($filters['date'])) {
                $date = Carbon::parse($filters['date']);
                $query->whereDate('start_at', $date);
            } else {
                $filters['date'] = Carbon::now();
                $query->whereDate('start_at', $filters['date']);
            }
            if (!empty($filters['status_id'])) {
                $query->where('trips.status_id', $filters['status_id']);
            }
            if (!empty($filters['provider_id'])) {
                $query->where('trips.provider_id', $filters['provider_id']);
            }
            if ($startPierId || !empty($filters['start_pier_id'])) {
                $query->where('start_pier_id', $startPierId ?? $filters['start_pier_id']);
            }
            if ($excursionId || !empty($filters['excursion_id'])) {
                $query->where('excursion_id', $excursionId ?? $filters['excursion_id']);
            }
            if ($excursionTypeId || !empty($filters['excursion_type_id'])) {
                $query->where('type_id', $excursionTypeId ?? $filters['excursion_id']);
            }
        }

        // current page automatically resolved from request via `page` parameter
        $trips = $query->paginate($request->perPage(10, $this->rememberKey));

        /** @var LengthAwarePaginator $trips */
        $trips->transform(function (Trip $trip) {
            /** @var TripChain $chain */
            $chain = $trip->chains->first();
            $chainStart = $chain?->trips()->min('start_at');
            $chainEnd = $chain?->trips()->max('start_at');

            return [
                'id' => $trip->id,
                'start_date' => $trip->start_at->format('d.m.Y'),
                'start_time' => $trip->start_at->format('H:i'),
                'excursion' => $trip->excursion->name,
                'excursion_type_id' => $trip->excursion->type_id,
                'pier' => $trip->startPier->name,
                'ship' => $trip->ship->name,
                'loading' => false,
                'tickets_count' => $trip->getAttribute('tickets_count'),
                'tickets_total' => $trip->tickets_total,
                'status' => $trip->status->name,
                'status_id' => $trip->status_id,
                'sale_status' => $trip->saleStatus->name,
                'has_rate' => $trip->hasRate(),
                'sale_status_id' => $trip->sale_status_id,
                'chained' => $trip->getAttribute('chains_count') > 0,
                'chain_trips_count' => $chain?->getAttribute('trips_count'),
                'chain_trips_start_at' => $chainStart ? Carbon::parse($chainStart)->format('d.m.Y') : null,
                'chain_trips_end_at' => $chainEnd ? Carbon::parse($chainEnd)->format('d.m.Y') : null,
                '_trip_start_at' => $trip->start_at->format('Y-m-d'),
                '_chain_end_at' => $chainEnd ? Carbon::parse($chainEnd)->format('Y-m-d') : null,
                'is_single_ticket' => $trip->excursion->is_single_ticket,
                'has_return_trip' => $trip->excursion->has_return_trip,
                'trip_provider' => $trip->excursion->additionalData?->provider_id,
                'reverse_excursion_id' => $trip->excursion->reverse_excursion_id,
                'stops' => StopResource::collection($trip->stops),
            ];
        });

        return APIResponse::list(
            $trips,
            ['Отправление', '№ рейса', 'Экскурсия', 'Причал, теплоход', 'Осталось билетов (всего)', 'Статусы движение / продажа'],
            $filters,
            $this->defaultFilters,
            [
                'date' => Carbon::parse($filters['date'])->setTimezone(config('app.timezone'))->format('d.m.Y'),
                'day' => Carbon::parse($filters['date'])->setTimezone(config('app.timezone'))->dayOfWeek,

                'excursions_filter' => $this->excursionFilter($filters),

                'piers_filter' => $this->piersFilter($filters),

            ])->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }

    private function piersFilter(array $filters)
    {
        $result = Pier::query()
            ->join('trips', function ($join) use ($filters) {
                $join->on('trips.start_pier_id', '=', 'piers.id');
//                    ->whereDate('trips.start_at', Carbon::parse($filters['date']));
            })
            ->join('excursions', 'excursions.id', '=', 'trips.excursion_id')
            ->where('excursions.status_id', 1)
            ->when($filters['excursion_type_id'], function ($query) use ($filters) {
                $query->where('type_id', $filters['excursion_type_id']);
            })
            ->when($filters['excursion_id'], function ($query) use ($filters) {
                $query->where('excursions.id', $filters['excursion_id']);
            })
            ->groupBy('piers.id')
            ->get(['piers.id', 'piers.name']);

        if ($result->isEmpty()) {
            return null;
        } else {
            return $result;
        }
    }

    private function excursionFilter(array $filters)
    {
        $result = Excursion::query()
            ->whereHas('trips', function ($trip) use ($filters) {
//                $trip->whereDate('start_at', Carbon::parse($filters['date']));
                $trip->when($filters['start_pier_id'], function ($query) use ($filters) {
                    $query->where('start_pier_id', $filters['start_pier_id']);
                });
            })
            ->when($filters['excursion_type_id'], function ($query) use ($filters) {
                $query->where('type_id', $filters['excursion_type_id']);
            })
            ->where('status_id', 1)
            ->get(['excursions.id', 'excursions.name']);

        if ($result->isEmpty()) {
            return null;
        } else {
            return $result;
        }
    }
}
