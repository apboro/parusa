<?php

namespace App\Http\Controllers\Showcase;

use App\Http\Controllers\ApiController;
use App\Http\Middleware\ExternalProtect;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Dictionaries\TripStatus;
use App\Models\Sails\Trip;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use JsonException;

class ShowcaseInitController extends ApiController
{
    /**
     * Initial data for showcase application.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws JsonException
     */
    public function init(Request $request): JsonResponse
    {
        $originalKey = $request->header(ExternalProtect::HEADER_NAME);

        try {
            $originalKey = $originalKey ? json_decode(Crypt::decrypt($originalKey), true, 512, JSON_THROW_ON_ERROR) : null;
        } catch (Exception $exception) {
            return response()->json(['message' => 'Ошибка сессии.'], 400);
        }

        $originalKey = [
            'ip' => $request->ip(),
            'user-agent' => $request->userAgent(),
            'is_partner' => $originalKey['is_partner'] ?? $request->input('is_partner'),
            'partner_id' => $originalKey['partner'] ?? $request->input('partner_id'),
            'media' => $originalKey['media'] ?? $request->input('media'),
        ];

        $programs = ExcursionProgram::query()->where(['enabled' => true])->select(['id', 'name'])->orderBy('name')->get();

        return response()->json([
            'today' => Carbon::now()->format('Y-m-d'),
            'programs' => $programs->toArray(),
            'date_from' => Carbon::now()->format('Y-m-d'),
            'date_to' => null,
        ], 200, [ExternalProtect::HEADER_NAME => Crypt::encrypt(json_encode($originalKey, JSON_THROW_ON_ERROR))]);
    }

    /**
     * Initial widget 2 data for showcase application.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws JsonException
     */
    public function init2(Request $request): JsonResponse
    {
        $originalKey = $request->header(ExternalProtect::HEADER_NAME);
        $currentDate = Carbon::now()->format('Y-m-d');

        try {
            $originalKey = $originalKey ? json_decode(Crypt::decrypt($originalKey), true, 512, JSON_THROW_ON_ERROR) : null;
        } catch (Exception $exception) {
            return response()->json(['message' => 'Ошибка сессии.'], 400);
        }

        $excursionsIDs = !empty($request->excursions) ? explode(',', $request->excursions) : null;

        $partner_id = $originalKey['partner'] ?? $request->input('partner_id');
        $forRootSite = $partner_id === null;
        $originalKey = [
            'ip' => $request->ip(),
            'user-agent' => $request->userAgent(),
            'is_partner' => $originalKey['is_partner'] ?? $request->input('is_partner'),
            'partner_id' => $originalKey['partner'] ?? $request->input('partner_id'),
            'excursions' => $originalKey['excursions'] ?? $request->input('excursions'),
            'media' => $originalKey['media'] ?? $request->input('media'),
        ];

        $programs = ExcursionProgram::query()
            ->where(['enabled' => true])
            ->select(['id', 'name'])
            ->orderBy('name')
            ->whereHas('excursions', function (Builder $builder) use ($excursionsIDs) {
                $builder->whereIn('excursions.id', $excursionsIDs);
            })
            ->get();


        $trips = Trip::query()
            ->whereIn('excursion_id', $excursionsIDs)
            ->whereIn('status_id', [TripStatus::regular])
            ->whereDate('start_at', '>=', $currentDate)
            ->whereHas('excursion.ratesLists', function (Builder $query) {
                $query
                    ->whereRaw('DATE(tickets_rates_list.start_at) <= DATE(trips.start_at)')
                    ->whereRaw('DATE(tickets_rates_list.end_at) >= DATE(trips.end_at)')
                    ->whereHas('rates', function (Builder $query) {
                        $query->where('grade_id', '!=', TicketGrade::guide);
                            $query->where('base_price', '>', 0);
                    });
            })
            ->whereHas('excursion', function (Builder $query) {
                $query->where('only_site', false);
            })
            ->get()
            ->sortBy('start_at');
        $tripsDate = $trips->pluck('start_at')->toArray();
        $dates = [];
        $today = Carbon::now()->format('Y-m-d');

        if (!empty($tripsDate)) {
            foreach ($tripsDate as $date) {
                if (Carbon::now() > $date) {
                    continue;
                }
                $dates[] = $date->format('Y-m-d');
            }
        }
        $dates = array_unique($dates);

        $tomorrow = Carbon::now()->addDay()->format('Y-m-d');
        $aftertomorrow = Carbon::now()->addDays(2)->format('Y-m-d');

        $items = [];
        if (count($trips) > 0) {
            $count = 1;

            foreach ($trips as $trip) {
                $start = $trip->start_at;
                if (Carbon::now() > $start) {
                    continue;
                }
                $date = (clone ($start))->format('Y-m-d');

                if ($today === $date) {
                    $title = 'Сегодня';
                } elseif ($tomorrow === $date) {
                    $title = 'Завтра';
                } elseif ($aftertomorrow === $date) {
                    $title = 'Послезавтра';
                } else {
                    $title = $start->translatedFormat('l');
                }

                if (isset($items[$date])) {
                    continue;
                }

                $items[$date] = [
                    'title' => $title,
                    'description' => "$start->day {$start->translatedFormat('F')}",
                    'date' => $date
                ];

                $count++;
                if ($count > 4) {
                    break;
                }

            }
        }
        $checked = count($dates) > 0 ? $dates[0] : null;
        $items = array_values($items);

        return response()->json([
            'today' => $today,
            'programs' => $programs->toArray(),
            'date_from' => $checked,
            'date_to' => null,
            'dates' => $dates,
            'items' => $items,
            'checked' => $checked,
        ], 200, [ExternalProtect::HEADER_NAME => Crypt::encrypt(json_encode($originalKey, JSON_THROW_ON_ERROR))]);
    }
}
