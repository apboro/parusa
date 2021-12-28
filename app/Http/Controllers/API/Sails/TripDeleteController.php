<?php

namespace App\Http\Controllers\API\Sails;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Sails\Trip;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        if ($id === null || null === ($trip = Trip::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Рейс не найден');
        }
        /** @var Trip $trip */

        try {
            $trip->delete();
        } catch (QueryException $exception) {
            return APIResponse::error("Невозможно удалить рейс №{$id}. Есть блокирующие связи.");
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::response([], [], "Рейс №{$id} удалён");
    }
}
