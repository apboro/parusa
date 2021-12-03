<?php

namespace App\Http\Controllers\API\Users;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\UserStatus;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class StaffListController extends ApiController
{
    /**
     * Get staff list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        $query = User::query()->with(['status', 'profile', 'staffPosition'])->where('is_staff', true);

        // apply filters
        // apply search

        // current page automatically resolved from request via `page` parameter
        $users = $query->paginate($request->perPage());

        /** @var Collection $users */
        $users->transform(function (User $user) {
            $profile = $user->profile;

            return [
                // TODO fix to mysql $partner->hasStatus(PartnerStatus::active)
                'active' => (int)$user->status_id === UserStatus::active,
                'id' => $user->id,
                'record' => [
                    'name' => $profile ? $profile->lastname . ' ' . $profile->firstname . ' ' . $profile->patronymic : null,
                    'position' => $user->staffPosition ? $user->staffPosition->position_title : null,
                    'contacts' => null,
                ],
            ];
        });

        return APIResponse::paginationList($users, [
            'name' => 'ФИО сотрудника',
            'position' => 'Должность',
            'contacts' => 'Контакты',
        ]);
    }
}
