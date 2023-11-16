<?php

namespace App\Http\Controllers\API\Ships;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\HitSource;
use App\Models\Hit\Hit;
use App\Models\Ships\Ship;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipDeleteController extends ApiController
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
        Hit::register(HitSource::admin);
        $id = $request->input('id');

        $ship = Ship::query()->where('id', $id)->first();
        if ($id === null || !$ship) {
            return APIResponse::notFound('Теплоход не найден');
        }

        $name = $ship->name;

        try {
            $ship->delete();
        } catch (QueryException $exception) {
            return APIResponse::error('Невозможно удалить теплоход. Есть блокирующие связи.');
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::response([], [], "Теплоход \"$name\" удалён");
    }
}
