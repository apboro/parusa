<?php

namespace App\Http\Controllers\API\Promoters;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\HitSource;
use App\Models\Hit\Hit;
use App\Models\Partner\Partner;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoterDeleteController extends ApiController
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
        Hit::register(HitSource::admin);
        $id = $request->input('id');

        /** @var Partner $partner */
        if ($id === null || null === ($partner = Partner::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Промоутер не найден');
        }

        try {
            $partner->positions()->first()->user()->delete();
            $partner->positions()->delete();
            $partner->delete();
        } catch (QueryException $exception) {
            return APIResponse::error('Невозможно удалить промоутера. Есть блокирующие связи.', ['error' => $exception->getMessage()]);
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success('Промоутер удалён');
    }
}
