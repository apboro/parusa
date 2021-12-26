<?php

namespace App\Http\Controllers\API\Sails;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Sails\Pier;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PierDeleteController extends ApiController
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

        if ($id === null || null === ($pier = Pier::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Причал не найден');
        }
        /** @var Pier $pier */

        $name = $pier->name;

        try {
            $pier->delete();
        } catch (QueryException $exception) {
            return APIResponse::error("Невозможно удалить причал \"{$name}\". Есть блокирующие связи.");
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::response([], [], "Причал \"{$name}\" удалён");
    }
}
