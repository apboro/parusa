<?php

namespace App\Http\Controllers\API\Sails;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Dictionaries\TicketStatus;
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

        $trip->tickets_total = $data['input'];
        $trip->save();

        return APIResponse::formSuccess('Данные обновлены', [
            'id' => $trip->id,
            'tickets_total' => $trip->tickets_total,
            'tickets_sold' => $trip->tickets()->whereIn('status_id', TicketStatus::ticket_sold_statuses)->count(),
            'tickets_reserved' => $trip->tickets()->whereIn('status_id', TicketStatus::ticket_reserved_statuses)->count(),
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
