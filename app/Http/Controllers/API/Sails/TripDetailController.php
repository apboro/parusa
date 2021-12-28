<?php

namespace App\Http\Controllers\API\Sails;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Sails\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripDetailController extends ApiEditController
{
    /**
     * Set tickets count.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function setTicketsCount(Request $request): JsonResponse
    {
        $data = $this->getData($request);
        $data['input'] = (int)$data['input'];

        $rules = ['input' => 'required|integer|min:1'];
        $titles = ['input' => 'Общее количество билетов'];

        if ($errors = $this->validate($data, $rules, $titles)) {
            return APIResponse::formError($data, $rules, $titles, $errors);
        }

        $id = $request->input('id');

        if ($id === null || null === ($trip = Trip::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Рейс не найден');
        }
        /** @var Trip $trip */

        $trip->tickets_count = $data['input'];
        $trip->save();

        return APIResponse::formSuccess('Данные обновлены', [
            'id' => $trip->id,
            'tickets_count' => $trip->tickets_count,
        ]);
    }

    /**
     * Set cancellation time.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function setCancellationTime(Request $request): JsonResponse
    {
        $data = $this->getData($request);
        $data['input'] = (int)$data['input'];

        $rules = ['input' => 'required|integer|min:0',];
        $titles = ['input' => 'Время аннулирования брони на рейс',];

        if ($errors = $this->validate($data, $rules, $titles)) {
            return APIResponse::formError($data, $rules, $titles, $errors);
        }

        $id = $request->input('id');

        if ($id === null || null === ($trip = Trip::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Рейс не найден');
        }
        /** @var Trip $trip */

        $trip->cancellation_time = $data['input'];
        $trip->save();

        return APIResponse::formSuccess('Данные обновлены', [
            'id' => $trip->id,
            'cancellation_time' => $trip->cancellation_time,
        ]);
    }
}
