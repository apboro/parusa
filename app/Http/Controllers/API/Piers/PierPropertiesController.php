<?php

namespace App\Http\Controllers\API\Piers;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\PiersStatus;
use App\Models\Sails\Pier;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PierPropertiesController extends ApiController
{
    /**
     * Update pier status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function properties(Request $request): JsonResponse
    {
        $id = $request->input('id');

        /** @var Pier $pier */
        if ($id === null || null === ($pier = Pier::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Причал не найден');
        }

        $name = $request->input('data.name');
        $value = $request->input('data.value');

        if (!$value || $name !== 'status_id') {
            return APIResponse::error('Неверно заданы параметры');
        }

        try {
            $pier->setStatus((int)$value);
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::response([], [
            'status' => $pier->status->name,
            'status_id' => $pier->status_id,
            'active' => $pier->hasStatus(PiersStatus::active),
        ], "Данные причала \"{$pier->name}\" обновлёны");
    }
}
