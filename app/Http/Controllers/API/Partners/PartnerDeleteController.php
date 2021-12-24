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

        if ($id === null || null === ($partner = Partner::query()->where('id', $id)->first())) {
            return APIResponse::notFound();
        }

        try {
            /** @var Partner $partner */
            $partner->delete();
        } catch (QueryException $exception) {
            return APIResponse::error('Невозможно удалить партнёра. Есть блокирующие связи.');
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::response('Партнёр удален');
    }
}
