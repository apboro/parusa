<?php

namespace App\Http\Controllers\Showcase;

use App\Http\Controllers\ApiController;
use App\Http\Middleware\ExternalProtect;
use App\Models\Common\Image;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
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
        $excursionsIDs = $request->input('excursions') !== null ? explode(',', $request->input('excursions')) : null;

        if ($date === null) {
            return response()->json(['message' => 'Не задана дата'], 500);
        }
        $date = Carbon::parse($date);

        $excursionId = $request->input('excursion_id');

        $listQuery = Trip::saleTripQuery($partnerId === null)
            ->with(['status', 'startPier', 'ship', 'excursion', 'excursion.info', 'excursion.programs'])
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
            $adult = $rateList->rates->where('grade_id', TicketGrade::adult)->first();
            $adultPrice = $partnerId === null ? $adult->site_price : $adult->base_price;

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

    public function trips2(Request $request): JsonResponse
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
        $excursionsIDs = $request->input('excursions') !== null ? explode(',', $request->input('excursions')) : null;
//        dd($request);
        if ($date === null) {
            return response()->json(['message' => 'Не задана дата'], 500);
        }
        $date = Carbon::parse($date);

        $listQuery = $this->baseTripQuery($partnerId === null)
            ->with(['status', 'startPier', 'ship', 'excursion', 'excursion.info', 'excursion.programs'])
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
            $adult = $rateList->rates->where('grade_id', TicketGrade::adult)->first();
            $adultPrice = $partnerId === null ? $adult->site_price : $adult->base_price;

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

    /**
     * Get trip info for showcase application.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function trip(Request $request): JsonResponse
    {
        $originalKey = $request->header(ExternalProtect::HEADER_NAME);

        try {
            $originalKey = $originalKey ? json_decode(Crypt::decrypt($originalKey), true, 512, JSON_THROW_ON_ERROR) : null;
        } catch (Exception $exception) {
            return response()->json(['message' => 'Ошибка сессии.'], 400);
        }

        $partnerId = $originalKey['partner_id'] ?? null;

        $id = $request->input('id');

        /** @var Trip $trip */
        $trip = Trip::saleTripQuery($partnerId === null)
            ->where('id', $id)
            ->with(['startPier', 'excursion', 'excursion.info', 'excursion.programs'])
            ->first();

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
                        'base_price' => $partnerId === null ? $rate->site_price : $rate->base_price,
                        'preferential' => $rate->grade->preferential,
                    ];
                });
        }

        return response()->json([
            'trip' => [
                'id' => $trip->id,
                'pier' => $trip->startPier->name,
                'pier_id' => $trip->start_pier_id,
                'start_date' => $trip->start_at->translatedFormat('j F Y') . ' г.',
                'start_time' => $trip->start_at->format('H:i'),
                'excursion' => $trip->excursion->name,
                'excursion_id' => $trip->excursion_id,
                'duration' => $trip->excursion->info->duration,
//                'images' => $trip->excursion->images->map(function (Image $image) {
//                    return $image->url;
//                }),
                'images' => null,
                'rates' => array_values($rates->toArray()),
            ],
        ]);
    }
}
