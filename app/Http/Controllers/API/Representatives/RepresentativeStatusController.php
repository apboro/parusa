<?php

namespace App\Http\Controllers\API\Representatives;

use App\Exceptions\Positions\WrongPositionAccessStatusException;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Positions\Position;
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
        $id = $request->input('position_id');

        /** @var Position $position */
        if ($id === null || null === ($position = Position::query()
                ->where(['id' => $id, 'is_staff' => false])
                ->first())
        ) {
            return APIResponse::notFound('Привязка представителя к организации не найдена.');
        }

        try {
            $position->setAccessStatus((int)$request->input('data.status_id'));
            $position->load('accessStatus');
        } catch (WrongPositionAccessStatusException $e) {
            return APIResponse::error('Неверный статус доступа представителя');
        }

        return APIResponse::success(
            'Статус доступа представителя обновлён',
            [
                'position_id' => $position->id,
                'status' => $position->accessStatus->name,
                'status_id' => $position->access_status_id,
                'active' => $position->hasStatus(PositionAccessStatus::active, 'access_status_id'),
            ]);
    }
}
