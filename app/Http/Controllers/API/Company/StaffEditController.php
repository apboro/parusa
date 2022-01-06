<?php

namespace App\Http\Controllers\API\Company;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffEditController extends ApiEditController
{
    protected array $rules = [
        'last_name' => 'required',
        'first_name' => 'required',
        'status_id' => 'required',
        'email' => 'required|email|bail',
        'gender' => 'required',
        'birthdate' => 'date|nullable',
    ];

    protected array $titles = [
        'last_name' => 'Фамилия',
        'first_name' => 'Имя',
        'patronymic' => 'Отчество',
        'status_id' => 'Статус трудоустройства',
        'position_title' => 'Должность',

        'birthdate' => 'Дата рождения',
        'gender' => 'Пол',

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
        $user = $this->firstOrNewUser($request, ['profile', 'staffPosition', 'staffPosition.status', 'staffPosition.staffInfo']);

        if ($user === null) {
            return APIResponse::notFound('Сотрудник не найен');
        }

        $profile = $user->profile;
        $position = $user->staffPosition;
        $info = $position->staffInfo;

        // send response
        return APIResponse::form(
            [
                'last_name' => $profile->lastname,
                'first_name' => $profile->firstname,
                'patronymic' => $profile->patronymic,
                'status_id' => $position->status_id,
                'position_title' => $position->title,
                'birthdate' => $profile->birthdate ? $profile->birthdate->format('d.m.Y') : null,
                'gender' => $profile->gender,
                'email' => $info->email,
                'work_phone' => $info->work_phone,
                'work_phone_additional' => $info->work_phone_additional,
                'mobile_phone' => $info->mobile_phone,
                'vkontakte' => $info->vkontakte,
                'facebook' => $info->facebook,
                'telegram' => $info->telegram,
                'skype' => $info->skype,
                'whatsapp' => $info->whatsapp,
                'notes' => $info->notes,
            ],
            $this->rules,
            $this->titles,
            [
                'title' => $user->exists ? $user->profile->fullName : 'Добавление сотрудника',
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
            return APIResponse::notFound('Сотрудник не найен');
        }

        $profile = $user->profile;
        $profile->lastname = $data['last_name'];
        $profile->firstname = $data['first_name'];
        $profile->patronymic = $data['patronymic'];
        $profile->birthdate = $data['birthdate'] === null ? null : Carbon::parse($data['birthdate'])->toDate();
        $profile->gender = $data['gender'];
        $profile->save();

        $position = $user->staffPosition;
        $position->setStatus($data['status_id']);
        $position->title = $data['position_title'];
        $position->save();

        $info = $position->staffInfo;
        $info->email = $data['email'];
        $info->work_phone = $data['work_phone'];
        $info->work_phone_additional = $data['work_phone_additional'];
        $info->mobile_phone = $data['mobile_phone'];
        $info->vkontakte = $data['vkontakte'];
        $info->facebook = $data['facebook'];
        $info->telegram = $data['telegram'];
        $info->skype = $data['skype'];
        $info->whatsapp = $data['whatsapp'];
        $info->notes = $data['notes'];
        $info->save();

        return APIResponse::formSuccess(
            $user->wasRecentlyCreated ? 'Сотрудник добавлен' : 'Данные сотрудника обновлены',
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
            ->has('staffPosition')
            ->first();

        return $user ?? null;
    }
}
