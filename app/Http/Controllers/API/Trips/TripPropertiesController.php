<?php

namespace App\Http\Controllers\API\Trips;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Hit\Hit;
use App\Models\Sails\Trip;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripPropertiesController extends ApiController
{
    /**
     * Update trip status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function properties(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $id = $request->input('id');

        /** @var Trip $trip */
        if ($id === null || null === ($trip = Trip::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Рейс не найден');
        }

        $name = $request->input('data.name');
        $value = $request->input('data.value');

        if (!$name || !$value || !in_array($name, ['status_id', 'sale_status_id', 'tickets_total', 'discount_status_id', 'cancellation_time'])) {
            return APIResponse::error('Неверно заданы параметры');
        }

        try {
            switch ($name) {
                case 'status_id':
                    $trip->setStatus((int)$value);
                    break;
                case 'sale_status_id':
                    $trip->setSaleStatus((int)$value);
                    break;
                case 'discount_status_id':
                    $trip->setDiscountStatus((int)$value);
                    break;
                case 'tickets_total':
                    if (!is_int($value) || $value < 0) {
                        return APIResponse::validationError(['value' => ['Неверное значение поля']]);
                    }
                    $trip->tickets_total = $value;
                    $trip->save();
                    break;
                case 'cancellation_time':
                    if (!is_int($value) || $value < 0) {
                        return APIResponse::validationError(['value' => ['Неверное значение поля']]);
                    }
                    $trip->cancellation_time = $value;
                    $trip->save();
                    break;
            }
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success("Данные рейса №$id обновлены", [
            'id' => $trip->id,
            'status' => $trip->status->name,
            'status_id' => $trip->status_id,
            'sale_status' => $trip->saleStatus->name,
            'sale_status_id' => $trip->sale_status_id,
            'tickets_total' => $trip->tickets_total,
            'discount_status' => $trip->discountStatus->name,
            'discount_status_id' => $trip->discount_status_id,
            'cancellation_time' => $trip->cancellation_time,
            'tickets_sold' => $trip->tickets()->whereIn('status_id', TicketStatus::ticket_had_paid_statuses)->count(),
            'tickets_reserved' => $trip->tickets()->whereIn('status_id', TicketStatus::ticket_reserved_statuses)->count(),
        ]);
    }
}
