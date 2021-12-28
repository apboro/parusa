<?php

namespace App\Http\Controllers\API\Sails;

use App\Exceptions\Sails\WrongExcursionStatusException;
use App\Exceptions\Sails\WrongTripDiscountStatusException;
use App\Exceptions\Sails\WrongTripSaleStatusException;
use App\Exceptions\Sails\WrongTripStatusException;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Sails\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripStatusController extends ApiController
{
    /**
     * Update trip status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function setStatus(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null || null === ($trip = Trip::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Рейс не найден');
        }
        /** @var Trip $trip */

        try {
            $trip->setStatus((int)$request->input('status_id'));
        } catch (WrongTripStatusException $e) {
            return APIResponse::error("Неверный статус рейса №{$id}");
        }

        return APIResponse::response([
            'status' => $trip->status->name,
            'status_id' => $trip->status_id,
        ], [], "Статус рейса №{$id} обновлён");
    }
    /**
     * Update trip ыфду status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function setSaleStatus(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null || null === ($trip = Trip::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Рейс не найден');
        }
        /** @var Trip $trip */

        try {
            $trip->setSaleStatus((int)$request->input('status_id'));
        } catch (WrongTripSaleStatusException $e) {
            return APIResponse::error("Неверный статус продаж рейса №{$id}");
        }

        return APIResponse::response([
            'status' => $trip->saleStatus->name,
            'status_id' => $trip->sale_status_id,
        ], [], "Статус продаж рейса №{$id} обновлён");
    }

    /**
     * Update trip вшысщгте status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function setDiscountStatus(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null || null === ($trip = Trip::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Рейс не найден');
        }
        /** @var Trip $trip */

        try {
            $trip->setDiscountStatus((int)$request->input('status_id'));
        } catch (WrongTripDiscountStatusException $e) {
            return APIResponse::error("Неверный статус скидок для рейса №{$id}");
        }

        return APIResponse::response([
            'status' => $trip->discountStatus->name,
            'status_id' => $trip->discount_status_id,
        ], [], "Статус скидок для рейса №{$id} обновлён");
    }
}
