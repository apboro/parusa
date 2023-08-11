<?php

namespace App\Http\Controllers\API\Trips;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Hit\Hit;
use App\Models\Sails\Trip;
use App\Models\Tickets\TicketRate;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class TripsSelectListController extends ApiController
{
    protected array $defaultFilters = [
        'date' => null,
        'program_id' => null,
        'excursion_id' => null,
        'start_pier_id' => null,
    ];

    protected array $rememberFilters = [
        'program_id',
        'excursion_id',
        'start_pier_id',
    ];

    protected string $rememberKey = CookieKeys::trips_select_list;

    /**
     * Get trips list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        $current = Currents::get($request);

        if ($current->terminal() !== null) {
            Hit::register(HitSource::terminal);
            $this->defaultFilters['start_pier_id'] = $current->terminal()->pier_id;
            $this->rememberFilters = [];
        } else {
            Hit::register(HitSource::partner);
        }

        $this->defaultFilters['date'] = Carbon::now()->format('Y-m-d');

        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey);

        $date = Carbon::parse($filters['date'] ?? null);

        $now = Carbon::now();

        $query = Trip::query()
            ->with(['startPier', 'excursion', 'excursion.programs', 'excursion.ratesLists'])
            ->withCount(['tickets' => function (Builder $query) {
                $query->whereIn('status_id', TicketStatus::ticket_countable_statuses);
            }])
            // filter actual trips
            ->where('trips.status_id', TripStatus::regular)
            ->where('sale_status_id', TripSaleStatus::selling)
            ->whereDate('start_at', $date)
            ->when(env('REMOVE_NEVA_TRIPS'), function (Builder $query) {
                $query->where('source', null);
            })
            ->where(function(Builder $trip) use ($now){
                $trip->where('start_at', '>', $now)
                    ->orWhere(function (Builder $trip) use ($now) {
                        $trip->where('end_at', '>', $now)
                        ->whereHas('excursion', function (Builder $excursion) use ($now) {
                            $excursion->where('is_single_ticket', 1);
                        });
                    });
                })

            ->whereHas('excursion.ratesLists', function (Builder $query) use ($date) {
                $query->whereDate('start_at', '<=', $date)->whereDate('end_at', '>=', $date);
            })
            ->whereHas('excursion', function ($query) {
                $query->where('only_site', false);
            })
            ->join('excursions', 'excursions.id', '=', 'trips.excursion_id')
            ->groupByRaw("case when `is_single_ticket`=1 THEN excursion_id else trips.id end")
            ->orderBy('is_single_ticket', 'desc');

        // apply filters
        if (!empty($filters['program_id'])) {
            $query->whereHas('excursion', function (Builder $query) use ($filters) {
                $query->whereHas('programs', function (Builder $query) use ($filters) {
                    $query->where('id', $filters['program_id']);
                });
            });
        }
        if (!empty($filters['excursion_id'])) {
            $query->where('excursion_id', $filters['excursion_id']);
        }
        if (!empty($filters['start_pier_id'])) {
            $query->where('start_pier_id', $filters['start_pier_id']);
        }

        // current page automatically resolved from request via `page` parameter
        $trips = $query->orderBy('start_at')->paginate($request->perPage(10, $this->rememberKey));

        /** @var LengthAwarePaginator $trips */
        $trips->transform(function (Trip $trip) use ($date) {
            $excursion = $trip->excursion;
            $rateList = $excursion->rateForDate($date);
            $rates = !$rateList ? [] : $rateList->rates
                ->filter(function (TicketRate $rate) {
                    return $rate->grade_id === TicketGrade::guide || $rate->base_price > 0;
                })
                ->map(function (TicketRate $rate) {
                    return [
                        'id' => $rate->grade->id,
                        'name' => $rate->grade->name,
                        'value' => $rate->base_price,
                        'preferential' => $rate->grade->preferential,
                    ];
                })->toArray();

            return [
                'id' => $trip->id,
                'start_date' => $trip->start_at->format('d.m.Y'),
                'start_time' => $trip->start_at->format('H:i'),
                'excursion_id' => $excursion->id,
                'excursion' => $excursion->name,
                'programs' => $excursion->programs->map(function (ExcursionProgram $program) {
                    return $program->name;
                }),
                'pier_id' => $trip->start_pier_id,
                'pier' => $trip->startPier->name,
                'ship' => $trip->ship->name,
                'tickets_count' => $trip->getAttribute('tickets_count'),
                'tickets_total' => $trip->tickets_total,
                'rates' => array_values($rates),
                'status' => $trip->status->name,
                'status_id' => $trip->status_id,
                'sale_status' => $trip->saleStatus->name,
                'sale_status_id' => $trip->sale_status_id,
                'chained' => $trip->getAttribute('chains_count') > 0,
                'is_single_ticket' => $trip->excursion->is_single_ticket,
                'has_return_trip' => $trip->excursion->has_return_trip,
            ];
        });

        return APIResponse::list(
            $trips,
            [
                'start' => 'Отправление',
                'excursion' => 'Экскурсия',
                'pier' => 'Причал',
                'tickets' => 'Осталось билетов',
                'prices' => 'Цены на билеты',
            ],
            $filters,
            $this->defaultFilters,
            []
        )->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }
}
