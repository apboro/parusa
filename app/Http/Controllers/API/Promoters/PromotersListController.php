<?php

namespace App\Http\Controllers\API\Promoters;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Dictionaries\PartnerType;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Hit\Hit;
use App\Models\Partner\Partner;
use App\Models\Positions\Position;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class PromotersListController extends ApiController
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
        $query = Partner::query()
            ->with(['type', 'status', 'positions', 'positions.user.profile', 'positions.user'])
            ->where('type_id', PartnerType::promoter);


        // apply filters
        if (!empty($filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey))) {
            if (!empty($filters['partner_status_id'])) {
                $query->where('status_id', $filters['partner_status_id']);
            }
        }

        // apply search
        if (!empty($search = $request->search())) {
            $query->where(function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                        foreach ($search as $term) {
                            $query->where('name', 'LIKE', "%$term%");
                        }
                    })
                    ->orWhere('partners.id', $search)
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
            return [
                'id' => $partner->id,
                'active' => $partner->hasStatus(PartnerStatus::active),
                'name' => $partner->name,
                'type' => $partner->type->name,
                'balance' => $partner->account->amount,
                'limit' => $partner->account->limit,
            ];
        });

        return APIResponse::list($partners,
            [
                'name' => 'ФИО промоутера',
                'ID' => 'ID',
                'balance' => 'Лицевой счет',
                'limit' => 'Лимит',
            ],
            $filters,
            $this->defaultFilters,
        )->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }

}
