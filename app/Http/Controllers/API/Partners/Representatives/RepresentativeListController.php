<?php

namespace App\Http\Controllers\API\Partners\Representatives;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Positions\Position;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class RepresentativeListController extends ApiController
{
    protected string $rememberKey = 'representatives_list';

    /**
     * Get representatives list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        $query = User::query()
            ->with(['profile', 'positions'])
            ->with('positions.partner', function (HasOne $query) {
                $query->orderBy('name');
            })
            ->doesntHave('staffPosition')
            ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->select('users.*')
            ->orderBy('user_profiles.lastname', 'asc')
            ->orderBy('user_profiles.firstname', 'asc')
            ->orderBy('user_profiles.patronymic', 'asc');

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

        /** @var Collection $users */
        $users->transform(function (User $user) {
            $profile = $user->profile;

            $partners = [];
            foreach ($user->positions ?? [] as $position) {
                /** @var Position $position */
                $partners[] = [
                    'active' => $position->hasStatus(PositionAccessStatus::active, 'access_status_id'),
                    'id' => $position->partner_id,
                    'name' => $position->partner->name,
                    'position' => $position->title,
                    'email' => $position->info->email,
                    'work_phone' => [
                        'number' => $position->info->work_phone,
                        'additional' => $position->info->work_phone_additional,
                    ],
                    'mobile_phone' => $position->info->mobile_phone,
                ];
            }
            return [
                'id' => $user->id,
                'record' => [
                    'name' => $profile ? $profile->fullName : null,
                    'partners' => $partners,
                ],
            ];
        });

        return APIResponse::paginationList($users, [
            'name' => 'ФИО представителя',
            'position' => 'Компания-партнер, должность',
            'contacts' => 'Контакты',
        ])->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }
}
