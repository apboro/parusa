<?php

namespace App\Http\Controllers\ShowcaseV2;

use App\Http\Controllers\Showcase\ShowcaseTripsController;
use App\Http\Middleware\ExternalProtect;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Sails\Trip;
use App\Models\Tickets\TicketRate;
use App\Models\Tickets\TicketsRatesList;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ShowcaseV2TripsController extends ShowcaseTripsController
{
    public function trips(Request $request): JsonResponse
    {
        $originalKey = $request->header(ExternalProtect::HEADER_NAME);

        try {
            $originalKey = $originalKey ? json_decode(Crypt::decrypt($originalKey), true, 512, JSON_THROW_ON_ERROR) : null;
        } catch (Exception $exception) {
            return response()->json(['message' => 'Ошибка сессии.'], 400);
        }

        $partnerId = $originalKey['partner_id'] ?? null;

        $date = $request->input('search.date');
        $persons = $request->input('search.persons');

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
            ->orderBy('trips.start_at');

        $listQueryDup = $listQuery->clone();

        $trips = $listQuery
            ->where('trips.start_at', '>=', $date)
            ->where('trips.start_at', '<=', $date->clone()->addDay()->setTime(4, 30))
            ->get();

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
            /** @var TicketRate $adult */
            $adultPrice = $rateList->getShowcasePrice($partnerId);

            return [
                'id' => $trip->id,
                'start_time' => $trip->start_at->format('H:i'),
                'start_date' => $trip->start_at->translatedFormat('j F Y') . ' г.',
                'pier' => $trip->startPier->name,
                'pier_id' => $trip->start_pier_id,
                'ship' => $trip->ship->name,
                'excursion' => $trip->excursion->name,
                'excursion_id' => $trip->excursion_id,
                'programs' => $trip->excursion->programs->map(function (ExcursionProgram $program) {
                    return $program->name;
                }),
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
