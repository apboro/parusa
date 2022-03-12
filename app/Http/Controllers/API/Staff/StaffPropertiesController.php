<?php

namespace App\Http\Controllers\API\Staff;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Dictionaries\Role;
use App\Models\POS\Terminal;
use App\Models\User\Helpers\Currents;
use App\Models\User\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
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

        $current = Currents::get($request);

        try {
            switch ($name) {
                case 'status_id':
                    // Check self change status
                    if ($current->positionId() === $user->staffPosition->id && !$user->hasStatus((int)$value)) {
                        return APIResponse::error('Нельзя изменить свой статус трудоустройства.');
                    }
                    $user->staffPosition->setStatus((int)$value);
                    break;
                case 'roles':
                    // Check self turn off of admin
                    if ($current->positionId() === $user->staffPosition->id && !in_array(Role::admin, $value, true) && $user->staffPosition->hasRole(Role::admin)) {
                        return APIResponse::error('Нельзя отключить роль адимнистратора для себя.');
                    }
                    // Check turn off assigned terminal users
                    if (!in_array(Role::terminal, $value, true) && $user->staffPosition->hasRole(Role::terminal)
                        && Terminal::query()->whereHas('staff', function (Builder $query) use ($user) {
                            $query->where('position_id', $user->staffPosition->id);
                        })->count() > 0
                    ) {
                        return APIResponse::error('Нельзя отключить роль кассира, сотрудник привязан к кассе.');
                    }
                    $user->staffPosition->roles()->sync($value);
                    $user->touch();
                    break;
                default:
            }
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::response([], [
            'status' => $user->staffPosition->status->name,
            'status_id' => $user->staffPosition->status_id,
            'active' => $user->staffPosition->hasStatus(PositionStatus::active),
            'roles' => $user->staffPosition->roles->pluck('id')->toArray(),
        ], "Данные сотрудника обновлёны");
    }
}
