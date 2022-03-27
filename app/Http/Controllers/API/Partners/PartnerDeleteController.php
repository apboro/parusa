<?php

namespace App\Http\Controllers\API\Partners;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Partner\Partner;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerDeleteController extends ApiController
{
    /**
     * Delete partner.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $id = $request->input('id');

        /** @var Partner $partner */
        if ($id === null || null === ($partner = Partner::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Партнёр не найден');
        }

        try {
            $partner->delete();
        } catch (QueryException $exception) {
            return APIResponse::error('Невозможно удалить компанию-партнёра. Есть блокирующие связи.', ['error' => $exception->getMessage()]);
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success('Компания-партнёр удалена');
    }
}
