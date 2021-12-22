<?php

namespace App\Http\Controllers\API\Partners\Representatives;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RepresentativeAccessController extends ApiEditController
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
        if (($user = $this->getRepresentativeUser($request)) === null) {
            return APIResponse::notFound('Представитель не найден');
        }

        /** @var User $user */

        $user->login = null;
        $user->password = null;
        $user->save();

        return APIResponse::response([
            'has_access' => false,
            'login' => null,
            'message' => 'Доступ закрыт',
        ]);
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
        if (($user = $this->getRepresentativeUser($request)) === null) {
            return APIResponse::notFound('Представитель не найден');
        }

        // Validate data
        $data = $this->getData($request);

        if ($errors = $this->validate($data, $this->rules, $this->titles)) {
            return APIResponse::formError($data, $this->rules, $this->titles, $errors);
        }

        /** @var User $user */

        $user->login = $data['login'];
        $user->password = Hash::make($data['password']);
        $user->save();

        return APIResponse::formSuccess(
            'Доступ открыт',
            [
                'has_access' => true,
                'login' => $user->login,
            ]
        );
    }

    /**
     * Get representative user.
     *
     * @param Request $request
     *
     * @return  User|null
     */
    protected function getRepresentativeUser(Request $request): ?User
    {
        $id = $request->input('id');

        if ($id === null || null === ($user = User::query()
                ->where('id', $id)
                ->doesntHave('staffPosition')->first())
        ) {
            return null;
        }
        /** @var User $user */

        return $user;
    }
}
