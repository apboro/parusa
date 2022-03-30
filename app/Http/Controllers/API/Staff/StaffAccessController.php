<?php

namespace App\Http\Controllers\API\Staff;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffAccessController extends ApiEditController
{
    protected array $rules = [
        'login' => 'required|min:6|unique:users',
        'password' => 'required|min:6',
        'password_confirmation' => 'required|same:password',
    ];

    protected array $titles = [
        'login' => 'Логин',
        'password' => 'Новый пароль',
        'password_confirmation' => 'Подтверждение пароля',
    ];

    /**
     * Release staff user access.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function release(Request $request): JsonResponse
    {
        if (($user = $this->getStaffUser($request)) === null) {
            return APIResponse::notFound('Сотрудник не найден');
        }

        /** @var User $user */
        if ($user->id === $request->user()->id) {
            return APIResponse::error('Вы не можете отключить себе доступ.');
        }

        $user->login = null;
        $user->password = null;
        $user->save();

        return APIResponse::success('Доступ закрыт',
            [
                'has_access' => false,
                'login' => null,
            ]
        );
    }

    /**
     * Release staff user access.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function set(Request $request): JsonResponse
    {
        if (($user = $this->getStaffUser($request)) === null) {
            return APIResponse::notFound('Сотрудник не найден');
        }

        // Validate data
        $data = $this->getData($request);

        if ($errors = $this->validate($data, $this->rules, $this->titles)) {
            return APIResponse::validationError($errors);
        }

        /** @var User $user */

        $user->login = $data['login'];
        $user->password = Hash::make($data['password']);
        $user->save();

        return APIResponse::success(
            'Доступ открыт',
            [
                'has_access' => true,
                'login' => $user->login,
            ]
        );
    }

    /**
     * Get staff user.
     *
     * @param Request $request
     *
     * @return  User|null
     */
    protected function getStaffUser(Request $request): ?User
    {
        $id = $request->input('id');

        if ($id === null || null === ($user = User::query()
                ->where('id', $id)
                ->has('staffPosition')->first())
        ) {
            return null;
        }
        /** @var User $user */

        return $user;
    }
}
