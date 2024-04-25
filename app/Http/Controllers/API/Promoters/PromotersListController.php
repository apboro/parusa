<?php

namespace App\Http\Controllers\API\Promoters;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Dictionaries\PartnerType;
use App\Models\Dictionaries\Tariff;
use App\Models\Hit\Hit;
use App\Models\Partner\Partner;
use App\Models\User\Helpers\Currents;
use App\Models\WorkShift\WorkShift;
use Illuminate\Database\Eloquent\Builder;
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
        $current = Currents::get($request);

        Hit::register(HitSource::admin);

        $query = Partner::query()
            ->with(['type', 'status', 'positions', 'positions.user.profile', 'positions.user', 'profile'])
            ->with(['workShifts' => fn($q) => $q->orderBy('start_at', 'asc')])
            ->where('type_id', PartnerType::promoter);

        // apply filters
        if (!empty($filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey))) {
            if (!empty($filters['partner_status_id'])) {
                $query->where('partners.status_id', $filters['partner_status_id']);
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
        $promotersWithOpenedShift = $query->clone()->whereHas('workShifts', fn($q) => $q->whereNull('end_at'))->pluck('id');
        $partners = $query->orderBy('name')->get();

        /** @var LengthAwarePaginator $partners */
        $partners->transform(function (Partner $partner) {
            $openedShift = $partner->getOpenedShift();
            return [
                'id' => $partner->id,
                'active' => $partner->hasStatus(PartnerStatus::active),
                'name' => $partner->name,
                'type' => $partner->type->name,
                'balance' => $openedShift?->getShiftTotalPay() + $openedShift?->balance,
                'limit' => $partner->account->limit,
                'open_shift' => $partner->workShifts()->with('tariff')->whereNull('end_at')->first(),
                'promoter_commission_rate' => $partner->tariff()->first()?->commission,
                'pier_name' => $openedShift && $partner->profile->self_pay ?  'Самостоятельно' : $openedShift?->terminal->pier->name,
            ];
        });

        return APIResponse::list(
            new LengthAwarePaginator($partners, 1000, 1000),
            [
                'name' => 'ФИО промоутера',
                'ID' => 'ID',
                'balance' => 'Баланс',
                'commission' => 'Ставка',
                'opened_at' => 'Смена открыта'
            ],
            $filters,
            $this->defaultFilters,
            [
                'promotersWithOpenedShift' => $promotersWithOpenedShift,
                'tariffsCommissionsValues' => Tariff::visible()->active()->get()->pluck('commission')
            ]
        )->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }

}
