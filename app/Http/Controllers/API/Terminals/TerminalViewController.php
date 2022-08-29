<?php

namespace App\Http\Controllers\API\Terminals;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\PositionStatus;
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
            null === ($terminal = Terminal::query()->with(['status', 'pier', 'pier.info', 'staff', 'staff.user', 'staff.user.profile', 'staff.staffInfo'])->where('id', $id)->first())) {
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
            'workplace_id' => $terminal->workplace_id,
            'outlet_id' => $terminal->outlet_id,
            //'organization_id' => $terminal->organization_id,
            'show_all_orders' => $terminal->show_all_orders,
            'staff' => $terminal->staff->map(function (Position $position) {
                return [
                    'id' => $position->id,
                    'name' => $position->user->profile->fullName,
                    'active' => $position->hasStatus(PositionStatus::active),
                    'position' => $position->title,
                    'email' => $position->staffInfo->email,
                    'work_phone' => $position->staffInfo->work_phone,
                    'work_phone_add' => $position->staffInfo->work_phone_additional,
                    'mobile_phone' => $position->staffInfo->mobile_phone,
                    'has_access' => !empty($position->user->login) && !empty($position->user->password),
                ];
            }),
        ];

        // send response
        return APIResponse::response($values);
    }
}
