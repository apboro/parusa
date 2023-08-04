<?php

namespace App\Http\Controllers\API\Trips;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Hit\Hit;
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
        Hit::register(HitSource::admin);
        $this->defaultFilters['date'] = Carbon::now()->format('Y-m-d');
        $startPierId = $request->input('start_pier_id');
        $excursionId = $request->input('excursion_id');

        $query = Trip::query()
            ->with(['startPier', 'endPier', 'ship', 'excursion', 'status', 'saleStatus'])
            ->with(['chains' => function (BelongsToMany $query) {
                $query->withCount('trips');
            }])
            ->withCount(['chains', 'tickets' => function(Builder $query) {
                $query->whereIn('status_id', TicketStatus::ticket_countable_statuses);
            }])
            ->join('excursions','excursions.id','=', 'trips.excursion_id')
            ->groupByRaw("case when `is_single_ticket`=1 THEN excursion_id else trips.id end")
            ->orderBy('is_single_ticket', 'desc');

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
                $query->where('status_id', $filters['status_id']);
            }
            if ($startPierId || !empty($filters['start_pier_id'])) {
                $query->where('start_pier_id', $startPierId ?? $filters['start_pier_id']);
            }
            if ($excursionId || !empty($filters['excursion_id'])) {
                $query->where('excursion_id', $excursionId ?? $filters['excursion_id']);
            }
        }

        // current page automatically resolved from request via `page` parameter
        $trips = $query->paginate($request->perPage(10, $this->rememberKey));

        /** @var LengthAwarePaginator $trips */
        $trips->transform(function (Trip $trip) {
            /** @var TripChain $chain */
            $chain = $trip->chains->first();
            $chainStart = $chain ? $chain->trips()->min('start_at') : null;
            $chainEnd = $chain ? $chain->trips()->max('start_at') : null;

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
                'chain_trips_count' => $chain ? $chain->getAttribute('trips_count') : null,
                'chain_trips_start_at' => $chainStart ? Carbon::parse($chainStart)->format('d.m.Y') : null,
                'chain_trips_end_at' => $chainEnd ? Carbon::parse($chainEnd)->format('d.m.Y') : null,
                '_trip_start_at' => $trip->start_at->format('Y-m-d'),
                '_chain_end_at' => $chainEnd ? Carbon::parse($chainEnd)->format('Y-m-d') : null,
                'is_single_ticket' => $trip->excursion->is_single_ticket,
                'reverse_excursion_id' => $trip->excursion->reverse_excursion_id,
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
            ]
        )->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }
}
