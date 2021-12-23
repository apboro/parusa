<?php

namespace App\Http\Controllers\API\Partners\Representatives;

use App\Exceptions\Positions\WrongPositionAccessStatusException;
use App\Exceptions\Sails\WrongExcursionStatusException;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Positions\Position;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RepresentativeStatusController extends ApiController
{
    /**
     * Update representative user status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function setStatus(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null || null === ($user = User::query()
                ->with(['positions'])
                ->where('id', $id)
                ->doesntHave('staffPosition')->first())
        ) {
            return APIResponse::notFound('Представитель не найден');
        }

        $positionId = $request->input('position_id');

        if ($positionId === null || null === ($position = Position::query()
                ->where(['id' => $positionId, 'user_id' => $id])->first())
        ) {
            return APIResponse::notFound('Такая привязка пользователя к организации не найдена');
        }

        /** @var Position $position */

        try {
            $position->setAccessStatus((int)$request->input('status_id'));
            $position->save();
            $position->load('accessStatus');
        } catch (WrongPositionAccessStatusException $e) {
            return APIResponse::error('Неверный статус доступа представителя');
        }

        return APIResponse::response([
            'position_id' => $position->id,
            'status' => $position->accessStatus->name,
            'status_id' => $position->access_status_id,
            'active' => $position->hasStatus(PositionAccessStatus::active, 'access_status_id'),
            'message' => 'Статус доступа представителя обновлён',
        ]);
    }
}
