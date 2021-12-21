<?php

namespace App\Http\Controllers\API\Partners;

use App\Exceptions\Partner\WrongPartnerStatusException;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Partner\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerStatusController extends ApiController
{
    /**
     * Update partner status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function setStatus(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null || null === ($partner = Partner::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Партнёр не найден');
        }

        /** @var Partner $partner */
        try {
            $partner->setStatus((int)$request->input('status_id'));
            $partner->save();
        } catch (WrongPartnerStatusException $e) {
            return APIResponse::error('Неверный статус партнёра');
        }

        return APIResponse::response([
            'status' => $partner->status->name,
            'status_id' => $partner->status_id,
            'message' => 'Статус партнёра обновлён',
        ]);
    }
}
