<?php

namespace App\Http\Controllers\API\Staff;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Dictionaries\Role;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class StaffListController extends ApiController
{
    protected array $defaultFilters = [
        'position_status_id' => PositionStatus::active,
    ];

    protected array $rememberFilters = [
        'position_status_id',
    ];

    protected string $rememberKey = CookieKeys::staff_list;

    /**
     * Get staff list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        $query = User::query()
            ->with(['profile', 'staffPosition', 'staffPosition.staffInfo', 'staffPosition.roles'])
            ->has('staffPosition')
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->select('users.*')
            ->orderBy('user_profiles.lastname')
            ->orderBy('user_profiles.firstname')
            ->orderBy('user_profiles.patronymic');

        // apply filters
        if (!empty($filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey)) && !empty($filters['position_status_id'])) {
            $query->whereHas('staffPosition', function (Builder $query) use ($filters) {
                $query->where('status_id', $filters['position_status_id']);
            });
        }

        // apply search
        if (!empty($search = $request->search())) {
            foreach ($search as $term) {
                $query->where(function (Builder $query) use ($term) {
                    $query->whereHas('profile', function (Builder $query) use ($term) {
                        $query->where('lastname', 'LIKE', "%$term%")
                            ->orWhere('firstname', 'LIKE', "%$term%")
                            ->orWhere('patronymic', 'LIKE', "%$term%");
                    });
                });
            }
        }

        // current page automatically resolved from request via `page` parameter
        $users = $query->paginate($request->perPage(10, $this->rememberKey));

        /** @var LengthAwarePaginator $users */
        $users->transform(function (User $user) {
            $profile = $user->profile;
            $position = $user->staffPosition;
            $info = $position->staffInfo;

            return [
                'active' => $position->hasStatus(PositionStatus::active),
                'id' => $user->id,
                'name' => $profile ? $profile->lastname . ' ' . $profile->firstname . ' ' . $profile->patronymic : null,
                'position' => $position->title,
                'email' => $info->email,
                'work_phone' => $info->work_phone,
                'work_phone_add' => $info->work_phone_additional,
                'mobile_phone' => $info->mobile_phone,
                'has_access' => !empty($user->login) && !empty($user->password) && $position->hasStatus(PositionStatus::active),
                'roles' => $position->roles->map(function (Role $role) {
                    return $role->name;
                }),
            ];
        });

        return APIResponse::list(
            $users,
            [
                'name' => 'ФИО сотрудника',
                'position' => 'Должность',
                'contacts' => 'Контакты',
                'access' => 'Доступ в систему',
                'roles' => 'Роли',
            ],
            $filters,
            $this->defaultFilters,
            []
        )->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }
}
