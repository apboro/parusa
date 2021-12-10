<?php

namespace App\Http\Controllers\API\Company;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Staff\StaffUserPosition;
use App\Models\User\User;
use App\Models\User\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StaffEditController extends ApiController
{
    protected array $rules = [
        'lastname' => 'required',
        'firstname' => 'required',
        'position_status_id' => 'required',
    ];

    protected array $titles = [
        'lastname' => 'Фамилия',
        'firstname' => 'Имя',
        'patronymic' => 'Отчество',
        'position_title' => 'Должность',
        'position_status_id' => 'Статус трудоустройства',
        'birthdate' => 'Дата рождения',
    ];

    /**
     * Get edit data for staff user.
     * id === 0 is for new user
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function get(Request $request): JsonResponse
    {
        $id = $request->input('id');
        if ($id === null) {
            return APIResponse::notFound();
        }

        $id = (int)$id;

        /** @var User $user */

        if ($id === 0) {
            // new user
            $user = new User;
            $profile = new UserProfile;

        } else if (null !== ($user = User::query()->with(['profile', 'staffPosition'])->where(['id' => $id, 'is_staff' => true])->first())) {
            $profile = $user->profile;
        } else {
            return APIResponse::notFound();
        }

        // fill data
        $values = [
            'lastname' => $profile->lastname,
            'firstname' => $profile->firstname,
            'patronymic' => $profile->patronymic,
            'position_title' => $user->staffPosition ? $user->staffPosition->position_title : null,
            'position_status_id' => $user->staffPosition ? $user->staffPosition->status_id : PositionStatus::default,
            'birthdate' => $profile->birthdate ? $profile->birthdate->format('d.m.Y') : null,
        ];

        // send response
        return APIResponse::form(
            $values,
            $this->rules,
            $this->titles,
            [
                'title' => $user->exists ? $profile->fullName : 'Добавление сотрудника',
            ]
        );
    }

    /**
     * Update staff user data.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $data = $request->input('data', []);

        $validator = Validator::make($data, $this->rules, [], array_map(function ($value) {
            return '"' . strtolower($value) . '"';
        }, $this->titles));

        if ($validator->fails()) {
            return APIResponse::formError(
                $data,
                $this->rules,
                $this->titles,
                $validator->getMessageBag()->toArray()
            );
        }


        $id = $request->input('id');

        if ($id === null) {
            return APIResponse::notFound();
        }

        $id = (int)$id;

        if ($id === 0) {
            // new user
            $user = new User;
            $user->is_staff = true;
            $user->save();
            $user->profile()->create([
                'lastname' => $data['lastname'],
                'firstname' => $data['firstname'],
                'patronymic' => $data['patronymic'],
                'birthdate' => $data['birthdate'],
                // TODO fix with edit component
                'gender' => 'male',
            ]);
            $position = new StaffUserPosition(['position_title' => $data['position_title']]);
            $position->setStatus($data['position_status_id'], false);
            $user->staffPosition()->save($position);

            return APIResponse::formSuccess('Данные сотрудника обновлены', ['id' => $user->id]);
        }

        if (null === ($user = User::query()->with(['profile', 'staffPosition'])->where(['id' => $id, 'is_staff' => true])->first())) {
            return APIResponse::notFound();
        }
        /** @var User $user */
        $profile = $user->profile;

        $profile->setAttribute('lastname', $data['lastname']);
        $profile->setAttribute('firstname', $data['firstname']);
        $profile->setAttribute('patronymic', $data['patronymic']);
        $profile->setAttribute('birthdate', $data['birthdate']);
        $profile->save();

        $position = $user->staffPosition;
        $position->setAttribute('position_title', $data['position_title']);
        $position->setStatus($data['position_status_id'], true);

        return APIResponse::formSuccess('Данные сотрудника обновлены');
    }
}
