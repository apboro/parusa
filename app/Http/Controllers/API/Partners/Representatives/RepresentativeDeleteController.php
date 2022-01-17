<?php

namespace App\Http\Controllers\API\Partners\Representatives;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\User\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RepresentativeDeleteController extends ApiController
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

        if ($id === null || null === ($user = User::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Представитель не найден');
        }
        /** @var User $user */

        if ($user->staffPosition->exists) {
            return APIResponse::error('Невозможно удалить сотрудника головной организации как представителя.');
        }

        try {
            $user->delete();
        } catch (QueryException $exception) {
            return APIResponse::error('Невозможно удалить представителя. Есть блокирующие связи.');
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::response('Представитель удалён');
    }
}
