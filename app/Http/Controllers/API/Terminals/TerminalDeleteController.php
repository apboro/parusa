<?php

namespace App\Http\Controllers\API\Terminals;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\POS\Terminal;
use App\Models\Sails\Pier;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TerminalDeleteController extends ApiController
{
    /**
     * Delete terminal.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null || null === ($terminal = Terminal::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Касса не найдена');
        }
        /** @var Terminal $terminal */

        try {
            $terminal->delete();
        } catch (QueryException $exception) {
            return APIResponse::error("Невозможно удалить кассу №\"$id\". Есть блокирующие связи.");
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::response([], [], "Касса №\"$id\" удалёна");
    }
}
