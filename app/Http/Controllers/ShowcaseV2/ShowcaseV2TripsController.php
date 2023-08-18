<?php

namespace App\Http\Controllers\ShowcaseV2;

use App\Http\Controllers\Showcase\ShowcaseTripsController;
use App\Http\Middleware\ExternalProtect;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Hit\Hit;
use App\Models\Sails\Trip;
use App\Models\Tickets\TicketsRatesList;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ShowcaseV2TripsController extends ShowcaseTripsController
{
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

        $excursionsIDs = $request->input('excursions');
        if (!empty($excursionsIDs) && !is_array($excursionsIDs)) {
            $excursionsIDs = [$excursionsIDs];
        }

        if ($date === null) {
            return response()->json(['message' => 'Не задана дата'], 500);
        }
        $date = Carbon::parse($date);

        $listQuery = Trip::saleTripQuery($partnerId === null)
            ->with(['status', 'startPier', 'ship', 'excursion', 'excursion.info', 'excursion.programs'])
            ->where('excursions.status_id', 1)
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
            ->when(!empty($excursionsIDs), function (Builder $query) use ($excursionsIDs) {
                $query->whereHas('excursion', function (Builder $query) use ($excursionsIDs) {
                    $query->whereIn('id', $excursionsIDs);
                });
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
                [   'trips.*',
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
}
