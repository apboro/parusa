<?php

namespace App\Http\Controllers\API\Users;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffCardController extends ApiController
{
    public function get(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null ||
            null === ($user = User::query()->with(['profile', 'staffPosition', 'staffPosition.status'])->where(['id' => $id, 'is_staff' => true])->first())) {
            return APIResponse::notFound();
        }

        /** @var User $user */

        $profile = $user->profile;

        // fill data
        $values = [
            'last_name' => $profile->lastname,
            'first_name' => $profile->firstname,
            'patronymic' => $profile->patronymic,
            'full_name' => $profile->fullName,
            'compact_name' => $profile->compactName,
            'gender' => $profile->gender === 'male' ? 'мужской' : 'женский',
            'position_title' => $user->staffPosition ? $user->staffPosition->position_title : null,
            'position_status' => $user->staffPosition->status->name,
            'birth_date' => $profile->birthdate->format('d.m.Y'),
            'created_at' => $user->staffPosition->created_at->format('d.m.Y'),
        ];

        // send response
        return APIResponse::response($values);
    }
}
