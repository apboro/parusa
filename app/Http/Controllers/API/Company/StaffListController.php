<?php

namespace App\Http\Controllers\API\Company;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\PositionStatus;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class StaffListController extends ApiController
{
    protected array $defaultFilters = [
        'position_status_id' => PositionStatus::active,
    ];

    protected array $rememberFilters = [
        'position_status_id',
    ];

    protected string $rememberKey = 'staff_list';

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
            ->with(['profile', 'staffPosition'])
            ->where('is_staff', true)
            ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->select('users.*')
            ->orderBy('user_profiles.lastname', 'asc')
            ->orderBy('user_profiles.firstname', 'asc')
            ->orderBy('user_profiles.patronymic', 'asc');

        // apply filters
        if (!empty($filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey))) {
            if (!empty($filters['position_status_id'])) {
                $query->whereHas('staffPosition', function (Builder $query) use ($filters) {
                    $query->where('status_id', $filters['position_status_id']);
                });
            }
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

        /** @var Collection $users */
        $users->transform(function (User $user) {
            $profile = $user->profile;

            return [
                'active' => $user->staffPosition ? $user->staffPosition->hasStatus(PositionStatus::active) : null,
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
        ], [
            'filters' => $filters,
            'filters_original' => $this->defaultFilters,
        ])->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }
}
