<?php

namespace App\Http\Controllers\API\Partners\Representatives;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Positions\Position;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RepresentativeCardController extends ApiController
{
    public function get(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null ||
            null === ($user = User::query()
                ->with(['profile', 'positions', 'positions.accessStatus', 'positions.info', 'positions.partner'])
                ->where('id', $id)
                ->first())
        ) {
            return APIResponse::notFound('Представитель не найден');
        }

        /** @var User $user */
        $profile = $user->profile;
        $positions = $user->positions;

        // fill data
        $values = [
            'full_name' => $profile->fullName,
            'gender' => $profile->gender === 'male' ? 'мужской' : 'женский',
            'birth_date' => $profile->birthdate ? $profile->birthdate->format('d.m.Y') : null,
            'created_at' => $user->created_at->format('d.m.Y'),

            'default_position_title' => $profile->default_position_title,
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

            'positions' => $positions->map(function (Position $position) {
                return [
                    'position_id' => $position->id,
                    'partner_id' => $position->partner->id,
                    'partner' => $position->partner->name,
                    'title' => $position->title,
                    'work_phone' => $position->info->work_phone,
                    'work_phone_additional' => $position->info->work_phone_additional,
                    'email' => $position->info->email,
                    'status' => $position->accessStatus->name,
                    'status_id' => $position->access_status_id,
                    'active' => $position->hasStatus(PositionAccessStatus::active, 'access_status_id'),
                ];
            }),

            'has_access' => !empty($user->login) && !empty($user->password),
            'login' => $user->login,
        ];

        // send response
        return APIResponse::response($values);
    }
}
