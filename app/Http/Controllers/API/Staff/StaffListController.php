<?php

namespace App\Http\Controllers\API\Staff;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\PositionStatus;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use JsonException;

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
     *
     * @throws JsonException
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        $query = User::query()
            ->with(['profile', 'staffPosition', 'staffPosition.staffInfo'])
            ->has('staffPosition')
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->select('users.*')
            ->orderBy('user_profiles.lastname', 'asc')
            ->orderBy('user_profiles.firstname', 'asc')
            ->orderBy('user_profiles.patronymic', 'asc');

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
                'active' => $user->staffPosition ? $user->staffPosition->hasStatus(PositionStatus::active) : null,
                'id' => $user->id,
                'record' => [
                    'name' => $profile ? $profile->lastname . ' ' . $profile->firstname . ' ' . $profile->patronymic : null,
                    'position' => $position->title,
                    'contacts' => [
                        'email' => $info->email,
                        'work_phone' => $info->work_phone,
                        'work_phone_add' => $info->work_phone_additional,
                        'mobile_phone' => $info->mobile_phone,
                    ],
                    'has_access' => !empty($user->login) && !empty($user->password),
                ],
            ];
        });

        return APIResponse::list(
            $users,
            [
                'name' => 'ФИО сотрудника',
                'position' => 'Должность',
                'contacts' => 'Контакты',
                'access' => 'Доступ в систему',
            ],
            $filters,
            $this->defaultFilters,
            []
        )->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }
}
