<?php

namespace App\Http\Controllers\API\Sails;

use App\Exceptions\Sails\WrongPierStatusException;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Sails\Pier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PierStatusController extends ApiController
{
    /**
     * Update pier status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function setStatus(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null || null === ($pier = Pier::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Причал не найден');
        }

        /** @var Pier $pier */
        try {
            $pier->setStatus((int)$request->input('status_id'));
            $pier->save();
        } catch (WrongPierStatusException $e) {
            return APIResponse::error('Неверный статус причала');
        }

        return APIResponse::response([
            'status' => $pier->status->name,
            'status_id' => $pier->status_id,
            'message' => 'Статус причала обновлён',
        ]);
    }
}
