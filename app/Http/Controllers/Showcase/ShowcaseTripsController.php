<?php

namespace App\Http\Controllers\Showcase;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Http\Middleware\ExternalProtect;
use App\Models\Common\Image;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Hit\Hit;
use App\Models\Sails\Trip;
use App\Models\Tickets\TicketRate;
use App\Models\Tickets\TicketsRatesList;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ShowcaseTripsController extends ApiController
{
    /**
     * Get trips list for showcase application.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function trips(Request $request): JsonResponse
    {
        Hit::register(HitSource::showcase);
        $originalKey = $request->header(ExternalProtect::HEADER_NAME);

        try {
            $originalKey = $originalKey ? json_decode(Crypt::decrypt($originalKey), true, 512, JSON_THROW_ON_ERROR) : null;
        } catch (Exception $exception) {
            return response()->json(['message' => 'Ошибка сессии.'], 400);
        }

        $partnerId = $originalKey['partner_id'] ?? null;

        $date = $request->input('search.date');
        $persons = $request->input('search.persons');
        $programs = $request->input('search.programs');

        if ($date === null) {
            return response()->json(['message' => 'Не задана дата'], 500);
        }
        $date = Carbon::parse($date);

        $excursionId = $request->input('excursion_id');

        $listQuery = Trip::saleTripQuery($partnerId === null)
            ->with(['status', 'startPier', 'ship', 'excursion', 'excursion.info', 'excursion.programs'])
            ->where('excursions.status_id', 1)
            ->when($partnerId, function (Builder $query) use ($partnerId) {
                $query->whereHas('excursion', function (Builder $query) use ($partnerId) {
                    $query->whereDoesntHave('partnerShowcaseHide', function (Builder $query) use ($partnerId) {
                        $query->where('partner_id', $partnerId);
                    });
                });
            })
            ->when($excursionId !== null, function (Builder $query) use ($excursionId, $partnerId) {
                $query->where('excursion_id', $excursionId);
            })
            ->withCount(['tickets'])
            ->with('excursion.ratesLists', function (HasMany $query) use ($date) {
                $query
                    ->with('rates', function (HasMany $query) {
                        $query
                            ->with('grade')
                            ->where('base_price', '>', 0)
                            ->whereNotIn('grade_id', [TicketGrade::guide]);
                    })
                    ->whereDate('start_at', '<=', $date)
                    ->whereDate('end_at', '>=', $date);
            })
            ->when($programs, function (Builder $query) use ($programs) {
                $query->whereHas('excursion', function (Builder $query) use ($programs) {
                    $query->whereHas('programs', function (Builder $query) use ($programs) {
                        $query->where('id', $programs);
                    });
                });
            })
            ->leftjoin('excursions', 'excursions.id', '=', 'trips.excursion_id')
            ->groupByRaw("case when `is_single_ticket`=1 THEN excursion_id else trips.id end")
            ->orderBy('is_single_ticket', 'desc')
            ->orderBy('trips.start_at');

        $listQueryDup = $listQuery->clone();

        $trips = $listQuery
            ->where('trips.start_at', '>=', $date)
            ->where('trips.start_at', '<=', $date->clone()->addDay()->setTime(4, 30))
            ->get(
                ['trips.*',
                    DB::raw("GROUP_CONCAT(DISTINCT trips.start_at ORDER BY trips.start_at ASC SEPARATOR ', ') AS concatenated_start_at")
                ]
            );

        if ($persons) {
            $trips = $trips->filter(function (Trip $trip) use ($persons) {
                return $trip->tickets_total - $trip->getAttribute('tickets_count') >= $persons;
            });
        }

        if ($trips->count() === 0) {
            $next = $listQueryDup
                ->where('trips.start_at', '>=', $date)
                ->oldest('trips.start_at')
                ->value('trips.start_at');
            $next = $next ? Carbon::parse($next) : null;
        }

        $trips = $trips->map(function (Trip $trip) use ($partnerId) {
            /** @var TicketsRatesList $rateList */
            $rateList = $trip->excursion->ratesLists->first();
            $adultPrice = $rateList->getShowcasePrice($partnerId);

            $ticketsCountable = $trip->tickets()->whereIn('status_id', TicketStatus::ticket_countable_statuses)->count();
            $ticketsReserved = $trip->tickets()->whereIn('status_id', TicketStatus::ticket_reserved_statuses)->count();

            return [
                'id' => $trip->id,
                'start_time' => $trip->start_at->format('H:i'),
                'start_date' => $trip->start_at->translatedFormat('j F Y') . ' г.',
                'pier' => $trip->startPier->name,
                'pier_id' => $trip->start_pier_id,
                'ship' => $trip->ship->name,
                'excursion' => $trip->excursion->name,
                'is_single_ticket' => $trip->excursion->is_single_ticket,
                'reverse_excursion_id' => $trip->excursion->reverse_excursion_id,
                'concatenated_start_at' => $trip->concatenated_start_at,
                'excursion_id' => $trip->excursion_id,
                'programs' => $trip->excursion->programs->map(function (ExcursionProgram $program) {
                    return $program->name;
                }),
                'tickets_left' => $trip->tickets_total - $ticketsCountable - $ticketsReserved,
                'duration' => $trip->excursion->info->duration,
                'price' => $adultPrice ?? null,
                'status' => $trip->status->name,
            ];
        });

        return response()->json([
            'date' => $date->translatedFormat('j F Y') . ' г.',
            'trips' => array_values($trips->toArray()),
            'next_date' => isset($next) ? $next->format('Y-m-d') : null,
            'next_date_caption' => isset($next) ? $next->translatedFormat('j F Y') . ' г.' : null,
        ]);
    }

    /**
     * Get trip info for showcase application.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function trip(Request $request): JsonResponse
    {
        Hit::register(HitSource::showcase);
        $originalKey = $request->header(ExternalProtect::HEADER_NAME);

        try {
            $originalKey = $originalKey ? json_decode(Crypt::decrypt($originalKey), true, 512, JSON_THROW_ON_ERROR) : null;
        } catch (Exception $exception) {
            return response()->json(['message' => 'Ошибка сессии.'], 400);
        }

        $partnerId = $originalKey['partner_id'] ?? null;

        $id = $request->input('id');

        $trip = Trip::find($id);

        if ($trip->excursion->is_single_ticket) {
            $trip = $trip->getAllTripsOfExcursionAndPierOnDay()->orderBy('start_at')->first();
        } else {
            /** @var Trip $trip */
            $trip = Trip::saleTripQuery($partnerId === null)
                ->where('trips.id', $id)
                ->with(['startPier', 'excursion', 'excursion.info', 'excursion.programs'])
                ->first();
        }
        if ($trip === null) {
            return response()->json([
                'message' => 'Продажа билетов на этот рейс не осуществляется.',
            ], 404);
        }

        $rates = $trip->excursion->rateForDate($trip->start_at);

        if ($rates !== null) {
            $rates = $rates->rates
                ->filter(function (TicketRate $rate) use ($partnerId) {
                    return ($partnerId === null ? !empty($rate->site_price) : !empty($rate->base_price)) && $rate->grade_id !== TicketGrade::guide;
                })
                ->map(function (TicketRate $rate) use ($partnerId) {
                    return [
                        'grade_id' => $rate->grade_id,
                        'name' => $rate->grade->name,
                        'base_price' => $partnerId === null  ? $rate->site_price : $rate->partner_price ?? $rate->base_price,
                        'backward_price' => $rate->backward_price_type === 'fixed' ? $rate->backward_price_value : $rate->base_price * ($rate->backward_price_value/100),
                        'preferential' => $rate->grade->preferential,
                    ];
                });
        }

        $ticketsCountable = $trip->tickets()->whereIn('status_id', TicketStatus::ticket_countable_statuses)->count();
        $ticketsReserved = $trip->tickets()->whereIn('status_id', TicketStatus::ticket_reserved_statuses)->count();

        return response()->json([
            'trip' => [
                'id' => $trip->id,
                'pier' => $trip->startPier->name,
                'pier_id' => $trip->start_pier_id,
                'start_date' => $trip->start_at->translatedFormat('j F Y') . ' г.',
                'start_time' => $trip->start_at->format('H:i'),
                'ship_has_scheme' => $trip->ship->ship_has_seats_scheme,
                'capacity' => $trip->ship->capacity,
                'shipId' => $trip->ship->id,
                'categories' => $trip->getSeatCategories(),
                'seats' => $trip->getSeats(),
                'seat_tickets_grades' => $trip->getSeatGrades(),
                'excursion' => $trip->excursion->name,
                'is_single_ticket' => $trip->excursion->is_single_ticket,
                'reverse_excursion_id' => $trip->excursion->reverse_excursion_id,
                'concatenated_start_at' => $trip->getTripStarts(),
                'excursion_id' => $trip->excursion_id,
                'duration' => $trip->excursion->info->duration,
                'images' => $trip->excursion->images->map(function (Image $image) {
                    return $image->url;
                }),
                'tickets_left' => $trip->tickets_total - $ticketsCountable - $ticketsReserved,
                'rates' => array_values($rates->toArray()),
            ],
        ]);
    }

    public function getBackwardTrips(Request $request): JsonResponse
    {
        Hit::register(HitSource::showcase);

        $tripID = $request->input('tripId');
        $trip = Trip::find($tripID);

        $backwardExcursionID = $trip->excursion->reverse_excursion_id;

        $startAt = Carbon::parse($trip->start_at);

        $trips = Trip::query()
            ->withCount(['tickets' => function (Builder $query) {
                $query->whereIn('status_id', TicketStatus::ticket_countable_statuses);
            }])
            ->where('excursion_id', $backwardExcursionID)
            ->whereDate('start_at', $startAt)
            ->where('start_at', '>=', $trip->end_at)
            ->where('status_id', TripStatus::regular)
            ->where('sale_status_id', TripSaleStatus::selling)
            ->whereHas('excursion.ratesLists', function (Builder $query) use ($startAt) {
                $query->whereDate('start_at', '<=', $startAt)->whereDate('end_at', '>=', $startAt);
            })
            ->get();

        $trips = $trips->filter(function (Trip $trip) {
            return $trip->tickets_total >= $trip->getAttribute('tickets_count') + 1;
        })->sortBy('start_at')->values();

        /** @var LengthAwarePaginator $trips */
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
                '_trip_start_at' => $trip->start_at->format('Y-m-d'),
            ];
        });

        return APIResponse::response(['trips' => $trips]);
    }

}
