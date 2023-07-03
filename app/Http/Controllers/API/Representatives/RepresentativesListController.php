<?php

namespace App\Http\Controllers\API\Representatives;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Hit\Hit;
use App\Models\Positions\Position;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class RepresentativesListController extends ApiController
{
    protected string $rememberKey = CookieKeys::representatives_list;

    /**
     * Get representatives list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $query = User::query()
            ->with(['profile', 'positions', 'positions.info'])
            ->with('positions.partner', function (HasOne $query) {
                $query->orderBy('name');
            })
            ->with('staffPosition')
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->select('users.*')
            ->orderBy('user_profiles.lastname')
            ->orderBy('user_profiles.firstname')
            ->orderBy('user_profiles.patronymic');

        // apply search
        if (!empty($search = $request->search())) {
            $query->where(function (Builder $query) use ($search) {
                $query
                    ->where(function (Builder $query) use ($search) {
                        foreach ($search as $term) {
                            $query->whereHas('profile', function (Builder $query) use ($term) {
                                $query->where('lastname', 'LIKE', "%$term%")
                                    ->orWhere('firstname', 'LIKE', "%$term%")
                                    ->orWhere('patronymic', 'LIKE', "%$term%");
                            });
                        }
                    })
                    ->orWhere(function (Builder $query) use ($search) {
                        $query->whereHas('positions.partner', function (Builder $query) use ($search) {
                            $query->where(function (Builder $query) use ($search) {
                                foreach ($search as $term) {
                                    $query->orWhere('name', 'LIKE', "%$term%");
                                }
                            });
                        });
                    });
            });
        }

        // current page automatically resolved from request via `page` parameter
        $users = $query->paginate($request->perPage(10, $this->rememberKey));

        /** @var LengthAwarePaginator $users */
        $users->transform(function (User $user) {
            $profile = $user->profile;

            $positions = [];
            foreach ($user->positions as $position) {
                /** @var Position $position */
                $positions[] = [
                    'partner_id' => $position->partner_id,
                    'partner' => $position->partner->name,
                    'title' => $position->title,
                    'email' => $position->info->email,
                    'work_phone' => $position->info->work_phone,
                    'work_phone_additional' => $position->info->work_phone_additional,
                    'mobile_phone' => $position->info->mobile_phone,
                    'active' => $position->hasStatus(PositionAccessStatus::active, 'access_status_id'),
                ];
            }
            return [
                'id' => $user->id,
                'name' => $profile->fullName ?? null,
                'positions' => $positions,
                'is_staff' => $user->staffPosition->exists,
                'email' => $profile->email,
                'work_phone' => $profile->work_phone,
                'work_phone_additional' => $profile->work_phone_additional,
                'mobile_phone' => $profile->mobile_phone,
                'has_access' => !empty($user->login) && !empty($user->password),
            ];
        });

        return APIResponse::list($users,
            [
                'name' => 'ФИО представителя',
                'position' => 'Компания-партнер, должность',
                'contacts' => 'Контакты',
                'access' => 'Доступ в систему',
            ]
        )->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }
}
