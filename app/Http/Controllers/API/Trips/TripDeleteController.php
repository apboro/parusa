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

        if ($id === null || null === ($trip = Trip::query()->with(['chains'])->where('id', $id)->first())) {
            return APIResponse::notFound('Рейс не найден');
        }
        /** @var Trip $trip */

        /** @var TripChain $chain */
        $chain = $trip->chains->first();

        if ($request->input('chained', false) === false) {
            // Delete single trip

            // Check for ordered tickets
            if ($trip->tickets()->count() > 0) {
                return APIResponse::error('Нельзя удалить рейс. На него есть оформленные билеты.');
            }

            // Get trip ids to move to new chain
            $laterTripIds = $chain->trips()->where('start_at', '>', $trip->start_at)->pluck('id')->toArray();

            // Delete trip
            try {
                DB::transaction(static function () use ($trip) {
                    $trip->delete();
                });
            } catch (QueryException $exception) {
                return APIResponse::error("Невозможно удалить рейс №{$id}. Есть блокирующие связи.");
            } catch (Exception $exception) {
                return APIResponse::error($exception->getMessage());
            }

            // Detach later trips from chain and attach to new
            if ($chain->trips()->count() !== count($laterTripIds)) {
                $chain->trips()->detach($laterTripIds);
                $newChain = new TripChain;
                $newChain->save();
                $newChain->trips()->sync($laterTripIds);
                $newChainTripsCount = count($laterTripIds);
            }

            return APIResponse::formSuccess("Рейс №{$id} удалён." . (isset($newChainTripsCount) ? " $newChainTripsCount рейсов перенесено в новую цепочку." : ''));
        }

        // Delete chained trips

        $chainEnd = $request->input('chain_upto');

        if (!$chainEnd) {
            return APIResponse::error("Неверно указан диапазон дат.");
        }

        $chainEnd = Carbon::parse($chainEnd);

        // Check for ordered tickets
        $tripsToDelete = Trip::query()
            ->withCount('tickets')
            ->whereHas('chains', function (Builder $query) use ($chain) {
                $query->where('id', $chain->id);
            })
            ->where('start_at', ">=", $trip->start_at)
            ->whereDate('start_at', '<=', $chainEnd)
            ->get();

        $tripsWithTickets = $tripsToDelete->filter(function (Trip $trip) {
            return $trip->getAttribute('tickets_count') !== 0;
        });

        if ($tripsWithTickets->count() > 0) {
            $ids = $tripsWithTickets->pluck('id')->toArray();
            return APIResponse::error('В диапазоне есть рейсы с оформленнымы билетами. [' . implode(',', $ids) . ']');
        }

        // Get trip ids to move to new chain
        $laterTripIds = $chain->trips()->whereDate('start_at', '>', $chainEnd)->pluck('id')->toArray();

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

        // Detach later trips from chain and attach to new
        if ($chain->trips()->count() !== count($laterTripIds)) {
            $chain->trips()->detach($laterTripIds);
            $newChain = new TripChain;
            $newChain->save();
            $newChain->trips()->sync($laterTripIds);
            $newChainTripsCount = count($laterTripIds);
        }

        return APIResponse::formSuccess("Удалёно $deleteCount рейсов." . (isset($newChainTripsCount) ? " $newChainTripsCount рейсов перенесено в новую цепочку." : ''));
    }
}
