<?php

namespace App\Http\Controllers\API\Partners;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Hit\Hit;
use App\Models\Partner\Partner;
use App\Models\Positions\Position;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class PartnersListController extends ApiController
{
    protected array $defaultFilters = [
        'partner_status_id' => PartnerStatus::active,
    ];

    protected array $rememberFilters = [
        'partner_status_id',
    ];

    protected string $rememberKey = CookieKeys::partners_list;

    /**
     * Get partners list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(APIListRequest $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $query = Partner::query()->with(['type', 'status'])
            ->with('positions', function (HasMany $query) {
                $query->where('status_id', PositionStatus::active)
                    ->with(['user', 'user.profile'])
                    ->join('user_profiles', 'positions.user_id', '=', 'user_profiles.user_id')
                    ->select('positions.*')
                    ->orderBy('user_profiles.lastname')
                    ->orderBy('user_profiles.firstname')
                    ->orderBy('user_profiles.patronymic');
            });

        // apply filters
        if (!empty($filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey))) {
            if (!empty($filters['partner_status_id'])) {
                $query->where('status_id', $filters['partner_status_id']);
            }
            if (!empty($filters['partner_type_id'])) {
                $query->where('type_id', $filters['partner_type_id']);
            }
        }

        // apply search
        if (!empty($search = $request->search())) {
            $query->where(function (Builder $query) use ($search) {
                $query
                    ->where(function (Builder $query) use ($search) {
                        foreach ($search as $term) {
                            $query->where('name', 'LIKE', "%$term%");
                        }
                    })
                    ->orWhere(function (Builder $query) use ($search) {
                        $query->whereHas('positions.user.profile', function (Builder $query) use ($search) {
                            foreach ($search as $term) {
                                $query->where(function (Builder $query) use ($term) {
                                    $query->where('lastname', 'LIKE', "%$term%")
                                        ->orWhere('firstname', 'LIKE', "%$term%")
                                        ->orWhere('patronymic', 'LIKE', "%$term%");
                                });
                            }
                        });
                    });
            });
        }

        // current page automatically resolved from request via `page` parameter
        $partners = $query->orderBy('name')->paginate($request->perPage(10, $this->rememberKey));

        /** @var LengthAwarePaginator $partners */
        $partners->transform(function (Partner $partner) {
            $representatives = [];
            foreach ($partner->positions as $position) {
                /** @var Position $position */
                $representatives[] = [
                    'id' => $position->user->id,
                    'name' => $position->user->profile ? $position->user->profile->fullName : '—',
                    'active' => $position->hasStatus(PositionAccessStatus::active, 'access_status_id'),
                ];
            }
            return [
                'id' => $partner->id,
                'active' => $partner->hasStatus(PartnerStatus::active),
                'name' => $partner->name,
                'representatives' => $representatives,
                'type' => $partner->type->name,
                'balance' => $partner->account->amount,
                'limit' => $partner->account->limit,
            ];
        });

        // available types ids
        $types = Partner::query()->select('type_id')->distinct()->pluck('type_id')->toArray();

        return APIResponse::list($partners,
            [
                'name' => 'Название партнера',
                'representatives' => 'Представители партнера',
                'type' => 'Тип партнера',
                'balance' => 'Лицевой счет',
                'limit' => 'Лимит',
            ],
            $filters,
            $this->defaultFilters,
            [
                'available_types' => $types,
            ])->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }

}
