<?php

namespace App\Http\Controllers\API\Rates;

use App\Models\Tickets\TicketsRatesList;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use Exception;
use Illuminate\Database\QueryException;

class RateDeleteController extends ApiController
{
    /**
     * Delete rates list.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null || null === ($ratesList = TicketsRatesList::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Тариф не найден');
        }
        /** @var TicketsRatesList $ratesList */

        try {
            $ratesList->delete();
        } catch (QueryException $exception) {
            return APIResponse::error('Невозможно удалить тариф. Есть блокирующие связи.');
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::response([], [], "Тариф удалён");
    }
}
