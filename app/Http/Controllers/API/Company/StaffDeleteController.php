<?php

namespace App\Http\Controllers\API\Company;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use Exception;
use App\Models\User\User;
use Illuminate\Database\QueryException;

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
        $id = $request->input('id');

        if ($id === null || null === ($user = User::query()->where('id', $id)->has('staffPosition')->first())) {
            return APIResponse::notFound();
        }

        try {
            /** @var User $user */
            $user->delete();
        } catch (QueryException $exception) {
            return APIResponse::error('Невозможно удалить сотрудника. Есть блокирующие связи.');
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::response('Сотрудник удалён');
    }
}
