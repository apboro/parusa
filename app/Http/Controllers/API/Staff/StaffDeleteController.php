<?php

namespace App\Http\Controllers\API\Staff;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\HitSource;
use App\Models\Hit\Hit;
use App\Models\User\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffDeleteController extends ApiController
{
    /**
     * Delete staff user.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $id = $request->input('id');

        if ($id === null || null === ($user = User::query()->with('profile')->where('id', $id)->has('staffPosition')->first())) {
            return APIResponse::notFound('Сотрудник не найден');
        }
        /** @var User $user */

        if ($user->id === $request->user()->id) {
            return APIResponse::error('Вы не можете удалить свою учётную запись');
        }

        $name = $user->profile->fullName;

        try {
            $user->delete();
        } catch (QueryException $exception) {
            return APIResponse::error('Невозможно удалить сотрудника. Есть блокирующие связи.');
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::response([], [], "Сотрудник \"$name\" удалён");
    }
}
