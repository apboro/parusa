<?php

namespace App\Http\Controllers\API\Staff;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Dictionaries\Role;
use App\Models\Positions\StaffPositionInfo;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffViewController extends ApiController
{
    public function view(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null ||
            null === ($user = User::query()
                ->with(['profile', 'staffPosition', 'staffPosition.status', 'staffPosition.staffInfo'])
                ->where('id', $id)
                ->has('staffPosition')->first())
        ) {
            return APIResponse::notFound('Сотрудник не найен');
        }

        /** @var User $user */

        $profile = $user->profile;
        $position = $user->staffPosition;
        /** @var StaffPositionInfo $info */
        $info = $position->staffInfo;

        // fill data
        $values = [
            'full_name' => $profile->fullName,
            'gender' => $profile->gender === 'male' ? 'мужской' : 'женский',
            'position' => $position->title,
            'status' => $position->status->name,
            'status_id' => $position->status_id,
            'active' => $position->hasStatus(PositionStatus::active),
            'birth_date' => $profile->birthdate ? $profile->birthdate->format('d.m.Y') : null,
            'created_at' => $user->created_at->format('d.m.Y'),

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

            'has_access' => !empty($user->login) && !empty($user->password),
            'login' => $user->login,

            'roles' => $user->staffPosition->roles->pluck('id')->toArray(),
        ];

        // send response
        return APIResponse::response($values);
    }
}
