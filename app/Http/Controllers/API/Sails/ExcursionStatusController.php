<?php

namespace App\Http\Controllers\API\Sails;

use App\Exceptions\Sails\WrongExcursionStatusException;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Sails\Excursion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExcursionStatusController extends ApiController
{
    /**
     * Update excursion status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function setStatus(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null || null === ($excursion = Excursion::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Экскурсия не найдена');
        }

        /** @var Excursion $excursion */
        try {
            $excursion->setStatus((int)$request->input('status_id'));
            $excursion->save();
        } catch (WrongExcursionStatusException $e) {
            return APIResponse::error('Неверный статус экскурсии');
        }

        return APIResponse::response([
            'status' => $excursion->status->name,
            'status_id' => $excursion->status_id,
            'active' => $excursion->hasStatus(ExcursionStatus::active),
            'message' => 'Статус экскурсии обновлён',
        ]);
    }
}
