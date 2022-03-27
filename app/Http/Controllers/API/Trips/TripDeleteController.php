<?php

namespace App\Http\Controllers\API\Trips;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Sails\Trip;
use App\Models\Sails\TripChain;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TripDeleteController extends ApiController
{
    /**
     * Delete trip.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $id = $request->input('id');

        /** @var Trip $trip */
        if ($id === null || null === ($trip = Trip::query()->with(['chains'])->where('id', $id)->first())) {
            return APIResponse::notFound('Рейс не найден');
        }

        $mode = $request->input('mode');
        if (!in_array($mode, ['single', 'all', 'range'])) {
            return APIResponse::error('Неверные параметры');
        }

        /** @var TripChain $chain */
        $chain = $trip->chains->first();

        // Delete single trip
        if ($mode === 'single') {

            // Check for ordered tickets
            if ($trip->tickets()->count() > 0) {
                return APIResponse::error('Нельзя удалить рейс. На него есть оформленные билеты.');
            }

            // Get trip ids to move to new chain
            $laterTripIds = $chain ? $chain->trips()->where('start_at', '>', $trip->start_at)->pluck('id')->toArray() : null;

            // Delete trip
            try {
                DB::transaction(static function () use ($trip) {
                    $trip->delete();
                });
            } catch (QueryException $exception) {
                return APIResponse::error("Невозможно удалить рейс №$id. Есть блокирующие связи.");
            } catch (Exception $exception) {
                return APIResponse::error($exception->getMessage());
            }

            if ($chain) {
                // Detach later trips from chain and attach to new if count greater than 1
                $laterCount = count($laterTripIds);
                if ($chain->trips()->count() !== $laterCount) {
                    $chain->trips()->detach($laterTripIds);
                    if ($laterCount > 1) {
                        $newChain = new TripChain;
                        $newChain->save();
                        $newChain->trips()->sync($laterTripIds);
                        $newChainTripsCount = count($laterTripIds);
                    }
                }
                // Remove chain if it no longer actual
                if ($chain->trips()->count() <= 1) {
                    $chain->trips()->detach();
                    $chain->delete();
                }
            }

            return APIResponse::success("Рейс №$id удалён." . (isset($newChainTripsCount) ? " $newChainTripsCount рейсов перенесено в новую цепочку." : ''));
        }

        // Delete chained trips

        $chainEnd = $request->input('to');

        if (!$chainEnd) {
            return APIResponse::error("Неверно указан диапазон дат.");
        }

        $chainEnd = Carbon::parse($chainEnd)->setTimezone(config('app.timezone'));

        // Get trips list to delete
        $tripsToDelete = Trip::query()
            ->select(['id', 'start_at'])
            ->withCount('tickets')
            ->whereHas('chains', function (Builder $query) use ($chain) {
                $query->where('id', $chain->id);
            })
            ->when($mode === 'range', function (Builder $query) use ($trip, $chainEnd) {
                $query
                    ->where('start_at', ">=", $trip->start_at)
                    ->whereDate('start_at', '<=', $chainEnd);
            })
            ->get();

        $tripsWithTickets = $tripsToDelete->filter(function (Trip $trip) {
            return $trip->getAttribute('tickets_count') !== 0;
        });

        if ($tripsWithTickets->count() > 0) {
            return APIResponse::error('В диапазоне есть рейсы с оформленнымы билетами.');
        }

        // Get trip ids to move to new chain
        if ($mode === 'range') {
            $laterTripIds = $chain->trips()->whereDate('start_at', '>', $chainEnd)->pluck('id')->toArray();
        }

        // Delete trips
        $deleteCount = $tripsToDelete->count();

        try {
            DB::transaction(static function () use ($tripsToDelete) {
                foreach ($tripsToDelete as $trip) {
                    /** @var Trip $trip */
                    $trip->delete();
                }
            });
        } catch (QueryException $exception) {
            return APIResponse::error("Невозможно удалить рейсы. Есть блокирующие связи.");
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        if ($mode === 'range') {
            // Detach later trips from chain and attach to new if count greater than 1
            $laterCount = count($laterTripIds);
            if ($chain->trips()->count() !== $laterCount) {
                $chain->trips()->detach($laterTripIds);
                if ($laterCount > 1) {
                    $newChain = new TripChain;
                    $newChain->save();
                    $newChain->trips()->sync($laterTripIds);
                    $newChainTripsCount = count($laterTripIds);
                }
            }
            // Remove chain if it no longer actual
            if ($chain->trips()->count() <= 1) {
                $chain->trips()->detach();
                $chain->delete();
            }
        } else {
            $chain->trips()->detach();
            $chain->delete();
        }

        return APIResponse::success("Удалёно $deleteCount рейсов." . (isset($newChainTripsCount) ? " $newChainTripsCount рейсов перенесено в новую цепочку." : ''));
    }
}
