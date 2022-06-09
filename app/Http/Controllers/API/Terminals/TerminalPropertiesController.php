<?php

namespace App\Http\Controllers\API\Terminals;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\TerminalStatus;
use App\Models\POS\Terminal;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TerminalPropertiesController extends ApiController
{
    /**
     * Update pier status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function properties(Request $request): JsonResponse
    {
        $id = $request->input('id');

        /** @var Terminal $terminal */
        if ($id === null || null === ($terminal = Terminal::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Касса не найдена');
        }

        $name = $request->input('data.name');
        $value = $request->input('data.value');

        if (!$value || $name !== 'status_id') {
            return APIResponse::error('Неверно заданы параметры');
        }

        try {
            $terminal->setStatus((int)$value);
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::response([], [
            'status' => $terminal->status->name,
            'status_id' => $terminal->status_id,
            'active' => $terminal->hasStatus(TerminalStatus::enabled),
        ], "Данные кассы №\"$id\" обновлены");
    }
}
