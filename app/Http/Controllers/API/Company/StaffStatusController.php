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
                ->with(['staffPosition', 'staffPosition.status'])
                ->where('id', $id)
                ->has('staffPosition')->first())
        ) {
            return APIResponse::notFound('Сотрудник не найден');
        }

        /** @var User $user */
        $position = $user->staffPosition;

        try {
            $position->setStatus((int)$request->input('status_id'));
            $position->save();
            $position->load('status');
        } catch (WrongPositionStatusException $e) {
            return APIResponse::error('Неверный статус трудоустройства сотрудника');
        }

        return APIResponse::response([
            'status' => $position->status->name,
            'status_id' => $position->status_id,
            'active' => $position->hasStatus(PositionStatus::active),
            'message' => 'Статус трудоустройства сотрудника обновлён',
        ]);
    }
}
