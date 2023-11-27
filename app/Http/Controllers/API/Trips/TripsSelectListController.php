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
use App\Models\Excursions\Excursion;
use App\Models\Hit\Hit;
use App\Models\Piers\Pier;
use App\Models\Sails\Trip;
use App\Models\Ships\Seats\TripSeat;
use App\Models\Tickets\TicketRate;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class TripsSelectListController extends ApiController
{
    protected array $defaultFilters = [
        'date' => null,
        'program_id' => null,
        'excursion_id' => null,
        'start_pier_id' => null,
        'excursion_type_id' => null,
    ];

    protected array $rememberFilters = [
        'program_id',
        'excursion_id',
        'start_pier_id',
        'excursion_type_id',
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
            ->with(['startPier', 'excursion', 'excursion.programs', 'excursion.ratesLists', 'ship', 'ship.seats'])
            ->withCount(['tickets' => function (Builder $query) {
                $query->whereIn('status_id', TicketStatus::ticket_countable_statuses);
            }])
            // filter actual trips
            ->where('trips.status_id', TripStatus::regular)
            ->where('excursions.status_id', 1)
            ->where('sale_status_id', TripSaleStatus::selling)
            ->whereDate('start_at', $date)
            ->when(env('REMOVE_NEVA_TRIPS'), function (Builder $query) {
                $query->where('source', null);
            })
            ->where(function (Builder $trip) use ($now) {
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
        if (!empty($filters['excursion_type_id'])) {
            $query->where('type_id', $filters['excursion_type_id']);
        }

        // current page automatically resolved from request via `page` parameter
        $trips = $query->orderBy('start_at')->paginate($request->perPage(10, $this->rememberKey));

        /** @var LengthAwarePaginator $trips */
        $trips->transform(function (Trip $trip) use ($date, $current) {
            $excursion = $trip->excursion;
            $rateList = $excursion->rateForDate($date);
            $rates = !$rateList ? [] : $rateList->rates
                ->filter(function (TicketRate $rate) use ($current) {
                    return $rate->grade_id === TicketGrade::guide || $rate->base_price > 0;
                })
                ->map(function (TicketRate $rate) use ($current) {
                    return [
                        'id' => $rate->grade->id,
                        'name' => $rate->grade->name,
                        'value' => ($rate->partner_price && ($current->isRepresentative() || $current->partnerId())) ? $rate->partner_price : $rate->base_price,
                        'preferential' => $rate->grade->preferential,
                    ];
                })->toArray();

            $categories = $trip->ship->seats()->whereNotNull('seat_category_id')->groupBy('seat_category_id')->get();
            if ($categories->isNotEmpty()) {
                $categories->transform(fn($e) => ['name' => $e->category?->name, 'id' => $e->category?->id]);
            } else {
                $categories = [];
            }


            return [
                'id' => $trip->id,
                'start_date' => $trip->start_at->format('d.m.Y'),
                'start_time' => $trip->start_at->format('H:i'),
                'excursion_id' => $excursion->id,
                'excursion_type_id' => $excursion->type_id,
                'excursion' => $excursion->name,
                'programs' => $excursion->programs->map(function (ExcursionProgram $program) {
                    return $program->name;
                }),
                'pier_id' => $trip->start_pier_id,
                'pier' => $trip->startPier->name,
                'ship' => $trip->ship->name,
                'capacity' => $trip->ship->capacity,
                'ship_has_scheme' =>$trip->ship->ship_has_seats_scheme,
                'shipId' => $trip->ship->id,
                'categories' => $categories,
                'seats' => $trip->ship->seats->transform(fn($seat) =>
                    ['seat_number' => $seat->seat_number,
                    'category' => $seat->category,
                    'status'=>$seat->status($trip->id)]),
                'seat_tickets_grades' => $trip->ship->seat_categories_ticket_grades()->with('grade')->get(),
                'tickets_count' => $trip->getAttribute('tickets_count'),
                'tickets_total' => $trip->tickets_total,
                'rates' => array_values($rates),
                'status' => $trip->status->name,
                'status_id' => $trip->status_id,
                'sale_status' => $trip->saleStatus->name,
                'sale_status_id' => $trip->sale_status_id,
                'chained' => $trip->getAttribute('chains_count') > 0,
                'is_single_ticket' => $trip->excursion->is_single_ticket,
                'reverse_excursion_id' => $trip->excursion->reverse_excursion_id,
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
            [
                'excursions_filter' => $this->excursionFilter($filters),

                'piers_filter' => $this->piersFilter($filters),

                'programs_filter' => $this->programsFilter($filters),

            ]
        )->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }

    private function piersFilter(array $filters)
    {
        $result = Pier::query()
            ->join('trips', function ($join) use ($filters) {
                $join->on('trips.start_pier_id', '=', 'piers.id')
                    ->whereDate('trips.start_at', Carbon::parse($filters['date']));
            })
            ->join('excursions', 'excursions.id', '=', 'trips.excursion_id')
            ->where('excursions.status_id', 1)
            ->when($filters['excursion_type_id'], function ($query) use ($filters) {
                $query->where('type_id', $filters['excursion_type_id']);
            })
            ->when($filters['excursion_id'], function ($query) use ($filters) {
                $query->where('excursions.id', $filters['excursion_id']);
            })
            ->when($filters['program_id'], function ($query) use ($filters) {
                $query->join('excursion_has_programs', 'excursions.id', '=', 'excursion_has_programs.excursion_id');
                $query->where('program_id', $filters['program_id']);
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
                $trip->whereDate('start_at', Carbon::parse($filters['date']));
                $trip->when($filters['start_pier_id'], function ($query) use ($filters) {
                    $query->where('start_pier_id', $filters['start_pier_id']);
                });
            })
            ->when($filters['excursion_type_id'], function ($query) use ($filters) {
                $query->where('type_id', $filters['excursion_type_id']);
            })
            ->when($filters['program_id'], function ($query) use ($filters) {
                $query->whereHas('programs', function ($query) use ($filters) {
                    $query->where('program_id', $filters['program_id']);
                });
            })
            ->where('status_id', 1)
            ->get(['excursions.id', 'excursions.name']);

        if ($result->isEmpty()) {
            return null;
        } else {
            return $result;
        }
    }

    private function programsFilter(array $filters)
    {
        $result = ExcursionProgram::query()
            ->join('excursion_has_programs', 'excursion_has_programs.program_id', '=', 'dictionary_excursion_programs.id')
            ->join('excursions', 'excursions.id', '=', 'excursion_has_programs.excursion_id')
            ->join('trips', function (JoinClause $join) use ($filters) {
                $join->on('trips.excursion_id', '=', 'excursions.id')
                    ->whereDate('trips.start_at', Carbon::parse($filters['date']));
            })
            ->when($filters['excursion_id'], function ($query) use ($filters) {
                $query->where('excursions.id', $filters['excursion_id']);
            })
            ->when($filters['start_pier_id'], function ($query) use ($filters) {
                $query->where('trips.start_pier_id', $filters['start_pier_id']);
            })
            ->when($filters['excursion_type_id'], function ($query) use ($filters) {
                $query->where('excursions.type_id', $filters['excursion_type_id']);
            })
            ->groupBy('dictionary_excursion_programs.id')
            ->get(['dictionary_excursion_programs.id', 'dictionary_excursion_programs.name']);
        if ($result->isEmpty()) {
            return null;
        } else {
            return $result;
        }
    }

}
