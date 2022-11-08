<?php

namespace App\Http\Controllers\API\Trips;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Sails\Trip;
use App\Models\Sails\TripChain;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripEditController extends ApiEditController
{
    protected array $rules = [
        'start_at' => 'date|required|bail',
        'end_at' => 'date|required|after:start_at|bail',
        'start_pier_id' => 'required',
        'end_pier_id' => 'required',
        'ship_id' => 'required',
        'excursion_id' => 'required',
        'tickets_total' => 'required|integer|min:1|bail',
        'discount_status_id' => 'required',
        'cancellation_time' => 'required|integer|min:0',
    ];

    protected array $titles = [
        'start_at' => 'Дата и время отправления',
        'end_at' => 'Дата и время прибытия',
        'start_pier_id' => 'Причал отправления',
        'end_pier_id' => 'Причал прибытия',
        'ship_id' => 'Теплоход',
        'excursion_id' => 'Экскурсия',
        'tickets_total' => 'Общее количество билетов',
        'discount_status_id' => 'Скидки от базовой цены на кассах',
        'cancellation_time' => 'Время аннулирования брони на рейс, мин.',
    ];

    /**
     * Get edit data for trip.
     * id === 0 is for new
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function get(Request $request): JsonResponse
    {
        /** @var Trip|null $trip */
        $trip = $this->firstOrNew(Trip::class, $request, ['chains']);

        // if new trip and set source trip get existing trip info as new
        $createFrom = ($trip !== null && !$trip->exists) ? $request->input('create_from') : null;

        if ($createFrom) {
            $trip = Trip::query()->where('id', $createFrom)->first();
        }

        if ($trip === null) {
            return APIResponse::notFound('Рейс не найден');
        }

        $new = !$trip->exists || $createFrom;

        /** @var TripChain $chain */
        $chain = $trip->chains->first();
        $chainStart = $chain ? $chain->trips()->min('start_at') : null;
        $chainEnd = $chain ? $chain->trips()->max('start_at') : null;

        // send response
        return APIResponse::form(
            [
                'start_at' => $trip->start_at ? $trip->start_at->format('Y-m-d\TH:i') : null,
                'end_at' => $trip->end_at ? $trip->end_at->format('Y-m-d\TH:i') : null,
                'start_pier_id' => $trip->start_pier_id,
                'end_pier_id' => $trip->end_pier_id,
                'ship_id' => $trip->ship_id,
                'excursion_id' => $trip->excursion_id,
                'tickets_total' => $trip->tickets_total,
                'discount_status_id' => $trip->discount_status_id,
                'cancellation_time' => $trip->cancellation_time,
            ],
            $this->rules,
            $this->titles,
            [
                'title' => !$new ? $trip->name : 'Добавление рейса',
                'chained' => $chain !== null,
                'chain_trips_start_at' => $chainStart ? Carbon::parse($chainStart)->format('d.m.Y') : null,
                'chain_trips_end_at' => $chainEnd ? Carbon::parse($chainEnd)->format('d.m.Y') : null,
                'chain_end_at' => $chainEnd ? Carbon::parse($chainEnd)->format('Y-m-d\TH:i') : null,
            ]
        );
    }

    /**
     * Update trip data.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $data = $this->getData($request);

        $mode = $request->input('mode');
        $to = $request->input('to');
        $days = $request->input('days', []);

        if (
            ($mode === 'range' && empty($to)) ||
            ($mode === 'weekly' && empty($to) && empty($days)) ||
            !in_array($mode, ['single', 'weekly', 'range'])
        ) {
            return APIResponse::error('Неверные параметры');
        }

        if ($errors = $this->validate($data, $this->rules, $this->titles)) {
            return APIResponse::validationError($errors);
        }

        $to = $to ? Carbon::parse($to)->startOfDay() : null;

        if ($mode !== 'single' && $to < Carbon::parse($data['start_at'])->startOfDay()) {
            return APIResponse::error('Неверно задан диапазон дат');
        }

        /** @var Trip|null $trip */
        $trip = $this->firstOrNew(Trip::class, $request);

        if ($trip === null) {
            return APIResponse::notFound('Рейс не найден');
        }

        // Create
        if (!$trip->exists) {
            $trip = $this->fillTrip($trip, $data, Carbon::parse($data['start_at']), Carbon::parse($data['end_at']));
            $trip->save();

            $message = 'Рейс добавлен';
            if (in_array($mode, ['range', 'weekly'])) {
                $count = 1;
                $count += $this->createChainedTrips($trip, $mode, $days, $to);
                $message = "Добавлено $count рейсов.";
            }

            return APIResponse::success($message, ['id' => $trip->id, 'title' => $trip->name]);
        }

        // Edit

        // check if editable

        /** @var TripChain $chain */
        $chain = $trip->chains->first();

        if ($chain === null && $mode !== 'single') {
            return APIResponse::error('Рейс не связан с другими рейсами.');
        }

        $tripsToEdit = Trip::query()
            ->withCount('tickets')
            ->when($mode !== 'single', function (Builder $query) use ($chain, $trip, $to) {
                $query->whereHas('chains', function (Builder $query) use ($chain) {
                    $query->where('id', $chain->id);
                })
                    ->where('start_at', ">=", $trip->start_at)
                    ->whereDate('start_at', '<=', Carbon::parse($to));
            })
            ->when($mode === 'single', function (Builder $query) use ($trip) {
                $query->where('id', $trip->id);
            })
            ->get();

        // Get trip with ordered tickets
        $tripsWithTickets = $tripsToEdit->filter(function (Trip $trip) {
            return $trip->getAttribute('tickets_count') !== 0;
        });

        if ($tripsWithTickets->count() > 0) {
            return APIResponse::error('В диапазоне есть рейсы с оформленными билетами.');
        }

        // iterate trips and update data
        $editedIds = [];
        $newStartAt = Carbon::parse($data['start_at'])->seconds(0)->milliseconds(0);
        $newEndAt = Carbon::parse($data['end_at'])->seconds(0)->milliseconds(0);

        foreach ($tripsToEdit as $editTrip) {
            /** @var Trip $editTrip */
            $editedIds[] = $editTrip->id;
            // operate on dates
            // date change is disabled for now
            $toSetStartAt = $editTrip->start_at->hours($newStartAt->hour)->minutes($newStartAt->minute)->seconds(0)->milliseconds(0);
            $toSetEndAt = $editTrip->end_at->hours($newEndAt->hour)->minutes($newEndAt->minute)->seconds(0)->milliseconds(0);
            $editTrip = $this->fillTrip($editTrip, $data, $toSetStartAt, $toSetEndAt);
            $editTrip->save();
        }
        // reattach edited to new chain
        // works in case date can not change

        if ($chain !== null) {
            $earlierTripIds = $chain->trips()->whereDate('start_at', '<', $trip->start_at->startOfDay())->pluck('id')->toArray();
            $laterTripIds = $chain->trips()->whereDate('start_at', '>', $to)->pluck('id')->toArray();

            if (count($earlierTripIds) === 1) {
                $chain->trips()->detach($earlierTripIds);
                if (count($laterTripIds) === 1) {
                    $chain->trips()->detach($laterTripIds);
                    if (count($editedIds) === 1) {
                        $chain->trips()->detach($editedIds);
                    }
                } else {
                    $chain->trips()->detach($editedIds);
                    if (count($editedIds) > 1) {
                        $newChain = new TripChain;
                        $newChain->save();
                        $newChain->trips()->sync($editedIds);
                    }
                }
            } else {
                if (count($laterTripIds) === 1) {
                    $chain->trips()->detach($laterTripIds);
                    $chain->trips()->detach($editedIds);
                    if (count($editedIds) > 1) {
                        $newChain = new TripChain;
                        $newChain->save();
                        $newChain->trips()->sync($editedIds);
                    }
                } else {
                    $chain->trips()->detach($laterTripIds);
                    $chain->trips()->detach($editedIds);
                    if (count($editedIds) > 1) {
                        $newChain = new TripChain;
                        $newChain->save();
                        $newChain->trips()->sync($editedIds);
                    }
                    $newChain = new TripChain;
                    $newChain->save();
                    $newChain->trips()->sync($laterTripIds);
                }
            }
            if ($chain->trips()->count() === 0) {
                $chain->delete();
            }
        }

        // response

        $count = count($editedIds);
        return APIResponse::success(
            $count === 1 ? 'Данные рейса обновлены.' : "Данные обновлены для $count рейсов.",
            [
                'trips' => $tripsToEdit,
            ],
        );
    }

    /**
     * Add chained trips.
     *
     * @param Trip $trip
     * @param string $mode
     * @param array $days
     * @param Carbon $to
     *
     * @return  int
     */
    protected function createChainedTrips(Trip $trip, string $mode, array $days, Carbon $to): int
    {
        $currentDate = $trip->start_at->clone()->startOfDay();

        if ($currentDate >= $to || !in_array($mode, ['range', 'weekly'])) {
            return 0;
        }

        $chain = new TripChain;
        $chain->save();

        $tripIds = [$trip->id];

        $duration = $trip->end_at->diffInMinutes($trip->start_at);

        while ($currentDate->addDay() <= $to) {
            if ($mode === 'range' || ($mode === 'weekly' && in_array($currentDate->dayOfWeek, $days, true))) {
                $newTrip = $trip->replicate();
                $newTrip->start_at = $newTrip->start_at->year($currentDate->year)->month($currentDate->month)->day($currentDate->day);
                $newTrip->end_at = $newTrip->start_at->clone()->addMinutes($duration);
                $newTrip->save();
                $tripIds[] = $newTrip->id;
            }
        }

        $chain->trips()->sync($tripIds);

        return count($tripIds) - 1;
    }

    /**
     * Fill trip from attributes.
     *
     * @param Trip $trip
     * @param array $data
     * @param Carbon $startAt
     * @param Carbon $endAt
     *
     * @return   Trip
     */
    protected function fillTrip(Trip $trip, array $data, Carbon $startAt, Carbon $endAt): Trip
    {
        $trip->start_at = $startAt;
        $trip->end_at = $endAt;
        $trip->start_pier_id = $data['start_pier_id'];
        $trip->end_pier_id = $data['end_pier_id'];
        $trip->ship_id = $data['ship_id'];
        $trip->excursion_id = $data['excursion_id'];
        $trip->setStatus(TripStatus::default, false);
        $trip->setSaleStatus(TripSaleStatus::default, false);
        $trip->tickets_total = $data['tickets_total'];
        $trip->discount_status_id = $data['discount_status_id'];
        $trip->cancellation_time = $data['cancellation_time'];

        return $trip;
    }
}
