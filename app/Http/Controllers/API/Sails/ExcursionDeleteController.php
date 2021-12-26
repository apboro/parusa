<?php

namespace App\Http\Controllers\API\Sails;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Sails\Excursion;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExcursionDeleteController extends ApiController
{
    /**
     * Delete staff user.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null || null === ($excursion = Excursion::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Экскурсия не найдена');
        }
        /** @var Excursion $excursion */

        $name = $excursion->name;

        try {
            $excursion->delete();
        } catch (QueryException $exception) {
            return APIResponse::error("Невозможно удалить экскурсию \"{$name}\". Есть блокирующие связи.");
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::response([], [], "Экскурсия \"{$name}\" удалена");
    }
}
