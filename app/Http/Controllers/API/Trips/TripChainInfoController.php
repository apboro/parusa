<?php

namespace App\Http\Controllers\API\Trips;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Hit\Hit;
use App\Models\Sails\Trip;
use App\Models\Sails\TripChain;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripChainInfoController extends ApiController
{
    /**
     * Get info about deleting trips.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function info(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        if (null === ($trip = Trip::query()
                ->with(['chains'])
                ->where('id', $request->input('id'))->first())) {
            return APIResponse::notFound('Рейс не найден');
        }
        /** @var Trip $trip */

        $mode = $request->input('mode');
        $to = $request->input('to');
        $from = $trip->start_at;

        if (($to === null && $mode === 'range') || !in_array($mode, ['single', 'all', 'range'])) {
            return APIResponse::error('Неверные параметры');
        }

        $to = Carbon::parse($to)->setTimezone(config('app.timezone'));
        if ($mode === 'range' && $to->startOfDay() < $trip->start_at->startOfDay()) {
            return APIResponse::error('Неверные параметры');
        }

        /** @var TripChain $chain */
        $chain = $trip->chains->first();
        if ($mode !== 'single' && $chain === null) {
            return APIResponse::error('Нет связанных рейсов.');
        }

        // Get trips range
        $tripsRange = Trip::query()
            ->withCount(['tickets' => function (Builder $query) {
                $query->whereIn('status_id', TicketStatus::ticket_countable_statuses);
            }])
            ->when($mode !== 'single', function (Builder $query) use ($chain) {
                $query->whereHas('chains', function (Builder $query) use ($chain) {
                    $query->where('id', $chain->id);
                });
            })
            ->when($mode === 'single', function (Builder $query) use ($trip) {
                $query->where('id', $trip->id);
            })
            ->when($mode === 'range', function (Builder $query) use ($from, $to) {
                $query->where('start_at', ">=", $from)->whereDate('start_at', '<=', $to);
            })
            ->get();

        // Get trip with ordered tickets
        $tripsWithTickets = $tripsRange->filter(function (Trip $trip) {
            return $trip->getAttribute('tickets_count') !== 0;
        });

        return APIResponse::response([
            'count' => $tripsRange->count(),
            'operable' => $tripsWithTickets->count() === 0,
            'blocked_by' => $tripsWithTickets->map(function (Trip $trip) {
                return [
                    'id' => $trip->id,
                    'start_at_date' => $trip->start_at->format('d.m.Y'),
                    'start_at_time' => $trip->start_at->format('H:i'),
                ];
            }),
        ]);
    }
}
