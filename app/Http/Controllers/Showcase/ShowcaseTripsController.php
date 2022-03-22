<?php

namespace App\Http\Controllers\Showcase;

use App\Http\Controllers\ApiController;
use App\Models\Common\Image;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Sails\Trip;
use App\Models\Tickets\TicketRate;
use App\Models\Tickets\TicketsRatesList;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $date = $request->input('search.date');
        $persons = $request->input('search.persons');
        $programs = $request->input('search.programs');

        if ($date === null) {
            return response()->json(['message' => 'Не задана дата'], 500);
        }
        $date = Carbon::parse($date);

        $trips = $this->baseTripQuery()
            ->with(['status', 'startPier', 'ship', 'excursion', 'excursion.info', 'excursion.programs'])
            ->withCount(['tickets'])
            ->with('excursion.ratesLists', function (HasMany $query) use ($date) {
                $query
                    ->with('rates', function (HasMany $query) {
                        $query
                            ->with('grade')
                            ->where('base_price', '>', 0)
                            ->whereNotIn('grade_id', [TicketGrade::guide]);
                    })
                    ->whereDate('start_at', '<=', $date)->whereDate('end_at', '>=', $date);
            })
            ->whereDate('trips.start_at', $date)
            ->when($programs, function (Builder $query) use ($programs) {
                $query->whereHas('excursion', function (Builder $query) use ($programs) {
                    $query->whereHas('programs', function (Builder $query) use ($programs) {
                        $query->where('id', $programs);
                    });
                });
            })
            ->orderBy('trips.start_at')
            ->get();

        if ($persons) {
            $trips = $trips->filter(function (Trip $trip) use ($persons) {
                return $trip->tickets_total - $trip->getAttribute('tickets_count') >= $persons;
            });
        }

        $trips = $trips->map(function (Trip $trip) {
            /** @var TicketsRatesList $rateList */
            $rateList = $trip->excursion->ratesLists->first();
            /** @var TicketRate $adult */
            $adult = $rateList->rates->where('grade_id', TicketGrade::adult)->first();

            return [
                'id' => $trip->id,
                'start_time' => $trip->start_at->format('H:i'),
                'pier' => $trip->startPier->name,
                'ship' => $trip->ship->name,
                'excursion' => $trip->excursion->name,
                'programs' => $trip->excursion->programs->map(function (ExcursionProgram $program) {
                    return $program->name;
                }),
                'duration' => $trip->excursion->info->duration,
                'price' => $adult->base_price ?? null,
                'status' => $trip->status->name,
            ];
        });

        return response()->json([
            'date' => $date->translatedFormat('j F Y'),
            'trips' => array_values($trips->toArray()),
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
        $id = $request->input('id');

        /** @var Trip $trip */
        $trip = $this->baseTripQuery()
            ->where('id', $id)
            ->with(['startPier', 'excursion', 'excursion.info', 'excursion.programs'])
            ->first();

        $rates = $trip->excursion->rateForDate($trip->start_at);

        if ($rates !== null) {
            $rates = $rates->rates
                ->filter(function (TicketRate $rate) {
                    return $rate->base_price !== 0 && $rate->grade_id !== TicketGrade::guide;
                })
                ->map(function (TicketRate $rate) {
                    return [
                        'grade_id' => $rate->grade_id,
                        'name' => $rate->grade->name,
                        'base_price' => $rate->base_price,
                    ];
                });
        }

        return response()->json([
            'trip' => [
                'id' => $trip->id,
                'excursion' => $trip->excursion->name,
                'start_date' => $trip->start_at->translatedFormat('j F Y'),
                'start_time' => $trip->start_at->format('H:i'),
                'pier' => $trip->startPier->name,
                'duration' => $trip->excursion->info->duration,
                'images' => $trip->excursion->images->map(function (Image $image) {
                    return $image->url;
                }),
                'rates' => $rates,
            ],
        ]);
    }

    /**
     * Actual trips query.
     *
     * @return  Builder
     */
    protected function baseTripQuery(): Builder
    {
        return Trip::query()
            ->where('start_at', '>', Carbon::now())
            ->whereIn('status_id', [TripStatus::regular])
            ->whereIn('sale_status_id', [TripSaleStatus::selling])
            ->whereHas('excursion.ratesLists', function (Builder $query) {
                $query
                    ->whereRaw('DATE(tickets_rates_list.start_at) <= DATE(trips.start_at)')
                    ->whereRaw('DATE(tickets_rates_list.end_at) >= DATE(trips.end_at)');
            });
    }
}
