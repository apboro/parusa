<?php

namespace App\Http\Controllers\API\Trips;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
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
        'status_id' => 'required',
        'sale_status_id' => 'required',
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
        'status_id' => 'Статус движения',
        'sale_status_id' => 'Статус продаж',
        'tickets_total' => 'Общее количество билетов',
        'discount_status_id' => 'Скидки от базовой цены на кассах',
        'cancellation_time' => 'Время аннулирования брони на рейс, мин.',
        'repeat_mode' => 'Повторять рейс',
        'repeat_days' => 'Дни',
        'repeat_until' => 'Повторять до (включительно)',
        'edit_chain_mode' => 'Применить корректировки для связанных рейсов',
        'edit_chain_upto' => 'Применить корректировки до (включительно)',
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

        $createFrom = ($trip && !$trip->exists) ? $request->input('create_from') : null;

        if ($createFrom) {
            $trip = Trip::query()->where('id', $createFrom)->get();
        }

        if ($trip === null) {
            return APIResponse::notFound('Рейс не найден');
        }

        $new = !$trip->exists || $createFrom;

        /** @var TripChain $chain */
        $chain = $trip->chains->first();
        $chainStart = $chain->trips()->min('start_at');
        $chainEnd = $chain->trips()->max('start_at');

        // send response
        return APIResponse::form(
            [
                'start_at' => $trip->start_at ? $trip->start_at->format('d.m.Y H:i') : null,
                'end_at' => $trip->end_at ? $trip->end_at->format('d.m.Y H:i') : null,
                'start_pier_id' => $trip->start_pier_id,
                'end_pier_id' => $trip->end_pier_id,
                'ship_id' => $trip->ship_id,
                'excursion_id' => $trip->excursion_id,
                'status_id' => $trip->status_id,
                'sale_status_id' => $trip->sale_status_id,
                'tickets_total' => $trip->tickets_total,
                'discount_status_id' => $trip->discount_status_id,
                'cancellation_time' => $trip->cancellation_time,
                'repeat_mode' => null,
                'repeat_days' => [],
                'repeat_until' => null,
                'edit_chain_mode' => false,
                'edit_chain_from' => null,
                'edit_chain_upto' => null,
            ],
            $this->rules,
            $this->titles,
            [
                'title' => !$new ? $trip->name : 'Добавление рейса',
                'chain_trips_count' => !$new ? $chain->trips()->count() : null,
                'chain_trips_start_at' => !$new && $chainStart ? Carbon::parse($chainStart)->format('d.m.Y') : null,
                'chain_trips_end_at' => !$new && $chainEnd ? Carbon::parse($chainEnd)->format('d.m.Y') : null,
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

        if ($errors = $this->validate($data, $this->rules, $this->titles)) {
            return APIResponse::formError($data, $this->rules, $this->titles, $errors);
        }

        /** @var Trip|null $trip */
        $trip = $this->firstOrNew(Trip::class, $request);

        if ($trip === null) {
            return APIResponse::notFound('Рейс не найден');
        }

        // Edit
        if ($trip->exists) {
            /** @var TripChain $chain */
            $chain = $trip->chains->first();

            if ($data['edit_chain_mode']) {
                // check range
                if (!($editUpTo = $data['edit_chain_upto'])) {
                    return APIResponse::error("Неверно указан диапазон дат.");
                }

                $tripsToEdit = Trip::query()
                    ->withCount('tickets')
                    ->whereHas('chains', function (Builder $query) use ($chain) {
                        $query->where('id', $chain->id);
                    })
                    ->where('start_at', ">=", $trip->start_at)
                    ->whereDate('start_at', '<=', Carbon::parse($editUpTo))
                    ->get();
            } else {
                $tripsToEdit = collect($trip);
            }

            // Check for ordered tickets
            $tripsWithTickets = $tripsToEdit->filter(function (Trip $trip) {
                return $trip->getAttribute('tickets_count') !== 0;
            });

            if ($tripsWithTickets->count() > 0) {
                $ids = $tripsWithTickets->pluck('id')->toArray();
                return APIResponse::error($data['edit_chain_mode']
                    ? 'В диапазоне есть рейсы с оформленнымы билетами. [' . implode(',', $ids) . ']'
                    : "Редактирование запрещено. Есть билеты, оформленные на этот рейс."
                );
            }

            // todo run update
            $count = $this->updateTripChain($tripsToEdit, $data);

            return APIResponse::formSuccess(
                "Данные обновлены для $count рейсов.",
                [
                    'id' => $trip->id,
                    'title' => $trip->name,
                ]
            );
        }

        // Create

        $trip = $this->fillTrip($trip, $data, Carbon::parse($data['start_at']), Carbon::parse($data['end_at']));
        $trip->save();

        $message = 'Рейс добавлен';

        if ($data['repeat_mode']) {
            $count = $this->createChainedTrips($trip, (int)$data['repeat_mode'], $data['repeat_days'], Carbon::parse($data['repeat_until']));
            $count++;
            $message = "Добавлено $count рейсов.";
        }

        return APIResponse::formSuccess(
            $message,
            [
                'id' => $trip->id,
                'title' => $trip->name,
            ]
        );
    }

    /**
     * Add chained trips.
     *
     * @param Trip $trip
     * @param int $repeatMode
     * @param array|null $days
     * @param Carbon $repeatUntil
     *
     * @return  int
     */
    protected function createChainedTrips(Trip $trip, int $repeatMode, ?array $days, Carbon $repeatUntil): int
    {
        $currentDate = $trip->start_at->clone()->hours(0)->minutes(0);

        if ($currentDate >= $repeatUntil || !in_array($repeatMode, [1, 2])) {
            return 0;
        }

        $chain = new TripChain;
        $chain->save();

        $tripIds = [$trip->id];

        $duration = $trip->end_at->diffInMinutes($trip->start_at);

        $days = array_map(static function ($item) {
            if ($item === '7') {
                return 0;
            }
            return (int)$item;
        }, $days);

        while ($currentDate->addDay() <= $repeatUntil) {
            if ($repeatMode === 1 || ($repeatMode === 2 && in_array($currentDate->dayOfWeek, $days))) {
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
        $trip->setStatus($data['status_id'], false);
        $trip->setSaleStatus($data['sale_status_id'], false);
        $trip->tickets_total = $data['tickets_total'];
        $trip->discount_status_id = $data['discount_status_id'];
        $trip->cancellation_time = $data['cancellation_time'];

        return $trip;
    }
}
