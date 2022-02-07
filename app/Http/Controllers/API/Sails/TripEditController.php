<?php

namespace App\Http\Controllers\API\Sails;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Sails\Trip;
use App\Models\Sails\TripChain;
use Carbon\Carbon;
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
        $trip = $this->firstOrNew(Trip::class, $request);

        if ($trip === null) {
            return APIResponse::notFound('Рейс не найден');
        }

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
            ],
            $this->rules,
            $this->titles,
            [
                'title' => $trip->exists ? $trip->name : 'Добавление рейса',
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

        $trip->start_at = Carbon::parse($data['start_at']);
        $trip->end_at = Carbon::parse($data['end_at']);
        $trip->start_pier_id = $data['start_pier_id'];
        $trip->end_pier_id = $data['end_pier_id'];
        $trip->ship_id = $data['ship_id'];
        $trip->excursion_id = $data['excursion_id'];
        $trip->setStatus($data['status_id'], false);
        $trip->setSaleStatus($data['sale_status_id'], false);
        $trip->tickets_total = $data['tickets_total'];
        $trip->discount_status_id = $data['discount_status_id'];
        $trip->cancellation_time = $data['cancellation_time'];

        $trip->save();

        $message = $trip->wasRecentlyCreated ? 'Рейс добавлен' : 'Данные рейса обновлены';

        if ($trip->wasRecentlyCreated && $data['repeat_mode']) {
            $count = $this->createChainedTrips($trip, (int)$data['repeat_mode'], $data['repeat_days'], Carbon::parse($data['repeat_until']));
            $count++;
            $message = "Добавлено {$count} рейсов.";
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
}
