<?php

namespace App\Http\Controllers\API\Company;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\User\User;
use App\Models\Dictionaries\PositionStatus;
use App\Exceptions\Positions\WrongPositionStatusException;

class StaffStatusController extends ApiController
{
    /**
     * Update staff user status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function setStatus(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null || null === ($user = User::query()
                ->with(['staffPosition', 'staffPosition.status', 'profile'])
                ->where('id', $id)
                ->has('staffPosition')->first())
        ) {
            return APIResponse::notFound('Сотрудник не найден');
        }
        /** @var User $user */

        /** @var User $user */
        if($user->id === $request->user()->id) {
            return APIResponse::error('Вы не можете изменять свой статус трудоустройства.');
        }

        $position = $user->staffPosition;
        $name = $user->profile->fullName;

        try {
            $position->setStatus((int)$request->input('status_id'));
            $position->save();
            $position->load('status');
        } catch (WrongPositionStatusException $e) {
            return APIResponse::error("Неверный статус трудоустройства сотрудника \"{$name}\"");
        }

        return APIResponse::response([
            'status' => $position->status->name,
            'status_id' => $position->status_id,
            'active' => $position->hasStatus(PositionStatus::active),
        ], [], "Статус трудоустройства сотрудника \"{$name}\" обновлён");
    }
}
