<?php

namespace App\Http\Controllers\ShowcaseV2;

use App\Http\Controllers\ApiController;
use App\Http\Middleware\ExternalProtect;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\HitSource;
use App\Models\Excursions\Excursion;
use App\Models\Hit\Hit;
use App\Models\Sails\Trip;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use JsonException;

class ShowcaseV2InitController extends ApiController
{
    /**
     * Initial widget 2 data for showcase application.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws JsonException
     */
    public function init(Request $request): JsonResponse
    {
        Hit::register(HitSource::showcase);
        $originalKey = $request->header(ExternalProtect::HEADER_NAME);

        try {
            $originalKey = $originalKey ? json_decode(Crypt::decrypt($originalKey), true, 512, JSON_THROW_ON_ERROR) : null;
        } catch (Exception $exception) {
            return response()->json(['message' => 'Ошибка сессии.'], 400);
        }

        $excursionsIDs = $request->input('excursions');
        if (!empty($excursionsIDs) && !is_array($excursionsIDs)) {
            $excursionsIDs = [$excursionsIDs];
        }

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

        $excursions = Excursion::whereIn('id', $excursionsIDs)->get(['id', 'name']);

        $programs = ExcursionProgram::query()
            ->where(['enabled' => true])
            ->select(['id', 'name'])
            ->orderBy('name')
            ->when(!empty($excursionsIDs), function (Builder $query) use ($excursionsIDs) {
                $query->whereHas('excursions', function (Builder $builder) use ($excursionsIDs) {
                    $builder->whereIn('excursions.id', $excursionsIDs);
                });
            })
            ->get();

        $tripsDates = Trip::saleTripQuery($forRootSite)
            ->when(!empty($excursionsIDs), function (Builder $query) use ($excursionsIDs) {
                $query->whereIn('excursion_id', $excursionsIDs);
            })
            ->orderBy('start_at')
            ->select(DB::raw('DATE(start_at) start_at'))
            ->groupBy(DB::RAW('DATE(start_at)'))
            ->pluck('start_at')
            ->toArray();

        $today = Carbon::now()->startOfDay();

        $items = [];

        if (count($tripsDates)) {
            foreach (array_chunk($tripsDates, 4)[0] as $date) {
                /** @var Carbon $date */
                $diff = $today->diffInDays($date);
                switch ($diff) {
                    case 0:
                        $title = 'Сегодня';
                        break;
                    case 1:
                        $title = 'Завтра';
                        break;
                    case 2:
                        $title = 'Послезавтра';
                        break;
                    default:
                        $title = $date->translatedFormat('l');
                }

                $items[] = [
                    'title' => $title,
                    'description' => "$date->day {$date->translatedFormat('F')}",
                    'date' => $date->format('Y-m-d'),
                ];
            }
        }

        $checked = count($tripsDates) > 0 ? $tripsDates[0]->format('Y-m-d') : null;

        return response()->json([
            'today' => $today->format('Y-m-d'),
            'programs' => $programs->toArray(),
            'date_from' => $checked,
            'date_to' => null,
            'dates' => array_map(function (Carbon $date) {
                return $date->format('Y-m-d');
            }, $tripsDates),
            'items' => $items,
            'checked' => $checked,
            'excursions' => $excursions,
        ], 200, [ExternalProtect::HEADER_NAME => Crypt::encrypt(json_encode($originalKey, JSON_THROW_ON_ERROR))]);
    }
}
