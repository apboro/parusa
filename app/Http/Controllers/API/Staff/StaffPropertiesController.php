<?php

namespace App\Http\Controllers\API\Staff;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Dictionaries\Role;
use App\Models\User\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffPropertiesController extends ApiController
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

        /** @var User $user */
        if ($id === null || null === ($user = User::query()->where('id', $id)->whereHas('staffPosition')->first())) {
            return APIResponse::notFound('Сотрудник не найден');
        }

        $name = $request->input('data.name');
        $value = $request->input('data.value');

        if (!in_array($name, ['status_id', 'roles'])) {
            return APIResponse::error('Неверно заданы параметры');
        }

        try {
            switch ($name) {
                case 'status_id':
                    $user->setStatus((int)$value);
                    break;
                case 'roles':
                    // TODO add role disabling check
                    // 1. self turn off of admin
                    // 2. turn off assigned terminal users
                    $user->staffPosition->roles()->sync($value);
                    $user->touch();
                    break;
                default:
            }
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::response([], [
            'status' => $user->status->name,
            'status_id' => $user->status_id,
            'active' => $user->hasStatus(PositionStatus::active),
            'roles' => $user->staffPosition->roles->pluck('id')->toArray(),
        ], "Данные сотрудника обновлёны");
    }
}
