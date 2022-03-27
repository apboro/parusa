<?php

namespace App\Http\Controllers\API\Representatives;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RepresentativeEditController extends ApiEditController
{
    protected array $rules = [
        'last_name' => 'required',
        'first_name' => 'required',
        'email' => 'required|email|bail',
        'gender' => 'required',
        'birthdate' => 'date|nullable',
    ];

    protected array $titles = [
        'last_name' => 'Фамилия',
        'first_name' => 'Имя',
        'patronymic' => 'Отчество',

        'birthdate' => 'Дата рождения',
        'gender' => 'Пол',

        'default_position_title' => 'Должность по умолчанию',

        'email' => 'Email',
        'work_phone' => 'Рабочий телефон',
        'work_phone_additional' => 'добавочный',
        'mobile_phone' => 'Мобильный телефон',
        'vkontakte' => 'VK',
        'facebook' => 'Facebook',
        'telegram' => 'Telegram',
        'skype' => 'Skype',
        'whatsapp' => 'WhatsApp',

        'notes' => 'Заметки',
    ];

    /**
     * Get edit data for excursion.
     * id === 0 is for new
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function get(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->firstOrNewUser($request, ['profile']);

        if ($user === null) {
            return APIResponse::notFound('Представитель не найден');
        }

        $profile = $user->profile;

        // send response
        return APIResponse::form(
            [
                'last_name' => $profile->lastname,
                'first_name' => $profile->firstname,
                'patronymic' => $profile->patronymic,
                'default_position_title' => $profile->default_position_title,

                'birthdate' => $profile->birthdate ? $profile->birthdate->format('Y-m-d') : null,
                'gender' => $profile->gender,

                'email' => $profile->email,
                'work_phone' => $profile->work_phone,
                'work_phone_additional' => $profile->work_phone_additional,
                'mobile_phone' => $profile->mobile_phone,
                'vkontakte' => $profile->vkontakte,
                'facebook' => $profile->facebook,
                'telegram' => $profile->telegram,
                'skype' => $profile->skype,
                'whatsapp' => $profile->whatsapp,
                'notes' => $profile->notes,
            ],
            $this->rules,
            $this->titles,
            [
                'title' => $user->exists ? $user->profile->fullName : 'Добавление представителя',
            ]
        );
    }

    /**
     * Update excursion data.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $data = $this->getData($request);

        if ($errors = $this->validate($data, $this->rules, $this->titles)) {
            return APIResponse::formError($data, $this->rules, $this->titles, $errors);
        }

        /** @var User|null $user */
        $user = $this->firstOrNewUser($request);

        if ($user === null) {
            return APIResponse::notFound('Представитель не найден');
        }

        $user->save();

        $profile = $user->profile;

        $profile->lastname = $data['last_name'];
        $profile->firstname = $data['first_name'];
        $profile->patronymic = $data['patronymic'];
        $profile->default_position_title = $data['default_position_title'];
        $profile->birthdate = Carbon::parse($data['birthdate'])->toDate();
        $profile->gender = $data['gender'];

        $profile->email = $data['email'];
        $profile->work_phone = $data['work_phone'];
        $profile->work_phone_additional = $data['work_phone_additional'];
        $profile->mobile_phone = $data['mobile_phone'];
        $profile->vkontakte = $data['vkontakte'];
        $profile->facebook = $data['facebook'];
        $profile->telegram = $data['telegram'];
        $profile->skype = $data['skype'];
        $profile->whatsapp = $data['whatsapp'];
        $profile->notes = $data['notes'];

        $profile->save();

        return APIResponse::success(
            $user->wasRecentlyCreated ? 'Представитель добавлен' : 'Данные представителя обновлены',
            [
                'id' => $user->id,
                'title' => $profile->fullName,
            ]
        );
    }

    /**
     * Retrieve user by id or create new.
     *
     * @param Request $request
     * @param array $with
     *
     * @return  User|null
     */
    protected function firstOrNewUser(Request $request, array $with = []): ?User
    {
        /** @var User $class */

        if (($id = $request->input('id')) === null) {
            return null;
        }

        $id = (int)$id;

        if ($id === 0) {
            return new User;
        }

        /** @var User $user */
        $user = User::query()
            ->where('id', $id)
            ->with($with)
            ->first();

        return $user ?? null;
    }
}
