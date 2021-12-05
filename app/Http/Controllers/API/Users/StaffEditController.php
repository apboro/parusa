<?php

namespace App\Http\Controllers\API\Users;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffEditController extends ApiController
{
    protected array $rules = [
        'last_name' => 'required|alpha|min:6|max:10',
        'first_name' => 'required',
        'status' => 'required',
    ];

    protected array $titles = [
        'last_name' => 'Фамилия',
        'first_name' => 'Имя',
        'patronymic' => 'Отчество',
        'position_title' => 'Должность',
        'position_status_id' => 'Статус трудоустройства',
        'birth_date' => 'Дата рождения',
    ];

    public function get(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null ||
            null === ($user = User::query()->with('profile')->where(['id' => $id, 'is_staff' => true])->first())) {
            return APIResponse::notFound();
        }

        /** @var User $user */

        $profile = $user->profile;

        // fill data
        $values = [
            'last_name' => $profile->lastname,
            'first_name' => $profile->firstname,
            'patronymic' => $profile->patronymic,
            'position_title' => $user->staffPosition ? $user->staffPosition->position_title : null,
            'position_status_id' => $user->staffPosition->status_id,
            'birth_date' => $profile->birthdate->format('d.m.Y'),
        ];

        // send response
        return APIResponse::form($values, $this->rules, $this->titles, [], [
            'full_name' => $profile->fullName,
        ]);
    }
}
