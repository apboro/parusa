<?php

namespace App\Http\Controllers\API\Terminals;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\TerminalStatus;
use App\Models\POS\Terminal;
use App\Models\Positions\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TerminalViewController extends ApiController
{
    public function view(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null ||
            null === ($terminal = Terminal::query()->with(['status', 'pier', 'pier.info', 'staff'])->where('id', $id)->first())) {
            return APIResponse::notFound('Касса не найдена');
        }

        /** @var Terminal $terminal */

        // fill data
        $values = [
            'id' => $terminal->id,
            'pier' => $terminal->pier->name,
            'address' => $terminal->pier->info->address,
            'active' => $terminal->hasStatus(TerminalStatus::enabled),
            'status' => $terminal->status->name,
            'status_id' => $terminal->status_id,
            'staff' => $terminal->staff->map(function (Position $position) {
                return [
                    'id' => $position->id,

                ];
            }),
        ];

        // send response
        return APIResponse::response($values);
    }
}
