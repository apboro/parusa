<?php

namespace App\Http\Controllers\API\Sails;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Sails\Trip;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripEditController extends ApiEditController
{
    protected array $rules = [
        'start_at' => 'required',
        'end_at' => 'required',
        'start_pier_id' => 'required',
        'end_pier_id' => 'required',
        'ship_id' => 'required',
        'excursion_id' => 'required',
        'status_id' => 'required',
        'sale_status_id' => 'required',
        'tickets_count' => 'required|integer|min:1',
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
        'tickets_count' => 'Общее количество билетов',
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
        $trip = $this->firstOrNew(Trip::class, $request);

        if ($trip === null) {
            return APIResponse::notFound('Рейс не найден');
        }

        // send response
        return APIResponse::form(
            [
                'start_at' => $trip->start_at->format('d.m.Y H:i'),
                'end_at' => $trip->end_at->format('d.m.Y H:i'),
                'start_pier_id' => $trip->start_pier_id,
                'end_pier_id' => $trip->end_pier_id,
                'ship_id' => $trip->ship_id,
                'excursion_id' => $trip->excursion_id,
                'status_id' => $trip->status_id,
                'sale_status_id' => $trip->sale_status_id,
                'tickets_count' => $trip->tickets_count,
                'discount_status_id' => $trip->discount_status_id,
                'cancellation_time' => $trip->cancellation_time,
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
            return APIResponse::notFound('Причал не найден');
        }

        $trip->start_at = Carbon::parse($data['start_at']);
        $trip->end_at = Carbon::parse($data['end_at']);
        $trip->start_pier_id = $data['start_pier_id'];
        $trip->end_pier_id = $data['end_pier_id'];
        $trip->ship_id = $data['ship_id'];
        $trip->excursion_id = $data['excursion_id'];
        $trip->setStatus($data['status_id'], false);
        $trip->setSaleStatus($data['sale_status_id'], false);
        $trip->tickets_count = $data['tickets_count'];
        $trip->discount_status_id = $data['discount_status_id'];
        $trip->cancellation_time = $data['cancellation_time'];

        $trip->save();

        return APIResponse::formSuccess(
            $trip->wasRecentlyCreated ? 'Рейс добавлен' : 'Данные рейса обновлены',
            [
                'id' => $trip->id,
                'title' => $trip->name,
            ]
        );
    }
}
