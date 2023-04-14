<?php

namespace App\Http\Controllers\API\Representatives;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\User\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class RepresentativeAccessController extends ApiEditController
{
    protected array $rules = [
        'login' => 'required|min:6|unique:users',
        'password' => 'required|min:6',
        'password_confirmation' => 'required|same:password',
        'email' => 'required_if:is_send_email,true|email',
    ];

    protected array $titles = [
        'login' => 'Логин',
        'password' => 'Новый пароль',
        'password_confirmation' => 'Подтверждение пароля',
        'email' => 'Email',
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
        if ($user->id === $request->user()->id) {
            return APIResponse::error('Вы не можете отключить себе доступ.');
        }

        /** @var User $user */

        $user->login = null;
        $user->password = null;
        $user->save();

        return APIResponse::success(
            'Доступ в систему для представителя закрыт',
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
        /** @var User $user */
        if (($user = $this->getRepresentativeUser($request)) === null) {
            return APIResponse::notFound('Представитель не найден');
        }

        // Validate data
        $data = $this->getData($request);

        if ($errors = $this->validate($data, $this->rules, $this->titles, [
            'email.required_if' => 'Обязательно для заполнения',
        ])) {
            return APIResponse::validationError($errors);
        }

        $messageIsSent = false;

        try {
            DB::transaction(function () use ($user, $data, &$messageIsSent) {
                $user->login = $data['login'];
                $user->password = Hash::make($data['password']);
                $user->save();

                if ($data['is_send_email'] && config('app.mail_send')) {
                    try {
                        Mail::send(
                            'email.invite',
                            [
                                'login' => $data['login'],
                                'password' => $data['password'],
                            ],
                            function ($message) use ($data) {
                                $message->to($data['email']);
                                $message->subject('Вы успешно зарегистрированы в системе «EXCURR»');
                            }
                        );
                        $messageIsSent = true;
                    } catch (Exception $exception) {
                        throw new RuntimeException('Ошибка отправки почты: ' . $exception->getMessage());
                    }
                }
            });
        } catch (RuntimeException $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success(
            'Доступ в систему для представителя открыт.' . ($messageIsSent ? ' Уведомление отправлено.' : null),
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
                ->first())
        ) {
            return null;
        }
        /** @var User $user */

        return $user;
    }
}
