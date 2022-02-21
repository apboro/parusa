<?php

namespace App\Http\Controllers\API\Excursions;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Sails\Excursion;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExcursionPropertiesController extends ApiController
{
    /**
     * Update excursion status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function properties(Request $request): JsonResponse
    {
        $id = $request->input('id');

        /** @var Excursion $excursion */
        if ($id === null || null === ($excursion = Excursion::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Экскурсия не найдена');
        }

        $name = $request->input('data.name');
        $value = $request->input('data.value');

        if (!$value || $name !== 'status_id') {
            return APIResponse::error('Неверно заданы параметры');
        }

        try {
            $excursion->setStatus((int)$value);
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::response([], [
            'status' => $excursion->status->name,
            'status_id' => $excursion->status_id,
            'active' => $excursion->hasStatus(ExcursionStatus::active),
        ], "Данные экскурсии \"$excursion->name\" обновлёны");
    }
}
