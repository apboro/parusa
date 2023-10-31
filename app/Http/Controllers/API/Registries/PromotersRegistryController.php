<?php

namespace App\Http\Controllers\API\Registries;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\PartnerType;
use App\Models\Hit\Hit;
use App\Models\Partner\Partner;
use App\Models\User\Helpers\Currents;
use App\Models\WorkShift\WorkShift;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class PromotersRegistryController extends ApiController
{
    protected array $defaultFilters = [
        'date_from' => null,
        'date_to' => null,
        'terminal_id' => null,
    ];
    protected array $rememberFilters = [
    ];

    public function __construct()
    {
        $this->defaultFilters['date_from'] = Carbon::now()->startOfMonth()->format('Y-m-d\TH:i');
        $this->defaultFilters['date_to'] = Carbon::now()->format('Y-m-d\TH:i');
    }

    protected string $rememberKey = CookieKeys::promoters_list;

    public function list(ApiListRequest $request): JsonResponse
    {
        Hit::register(HitSource::admin);

        $terminalId = $this->getTerminalId($request);

        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey);
        $query = Partner::query()
            ->with(['workShifts'])
            ->where('type_id', PartnerType::promoter);

        if ($terminalId) {
            $query->whereHas('workShifts', fn($q) => $q->where('terminal_id', $terminalId));
        }
        if ($request->input('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%')
                ->orWhere('id', 'like', $request->input('search') . '%');
        }

        if (!empty($filters['date_from'])) {
            $query->whereHas('workShifts', function ($query) use ($filters) {
                $query->where('start_at', '>=', Carbon::parse($filters['date_from']));
            });
        }
        if (!empty($filters['date_to'])) {
            $query->whereHas('workShifts', function ($query) use ($filters) {
                $query->where('start_at', '<=', Carbon::parse($filters['date_to']));
            });
        }

        $partners = $query->paginate($request->perPage(25, $this->rememberKey));

        $partners->transform(function (Partner $partner) {

            return [
                'id' => $partner->id,
                'name' => $partner->name,
                'pay_for_out' => $partner->workShifts->sum('pay_for_out'),
                'total_hours' => $partner->workShifts->sum(function ($shift) {
                    return $shift->getWorkingHours();
                }),
                'pay_for_time' => $partner->workShifts->sum(function ($shift) {
                    return $shift->getPayForTime();
                }),
                'sales_total' => $partner->workShifts->sum('sales_total'),
                'commission' => $partner->workShifts->sum('pay_commission'),
                'total_to_pay_out' => $partner->workShifts->sum(function ($shift) {
                    return $shift->getShiftTotalPay();
                }),
                'total_paid_out' => $partner->workShifts->sum('paid_out')
            ];
        });


        return APIResponse::list(
            $partners,
            ['ID', 'ФИО', 'За выход', 'Кол-во часов', 'Оплата за время', 'Касса', '% от кассы', 'Всего начислено', 'Получено'],
            $filters,
            $this->defaultFilters,
            [
                'total_to_pay_out' => $partners->sum('total_to_pay_out'),
                'total_paid_out' => $partners->sum('total_paid_out')
            ])->withCookie(cookie($this->rememberKey, $request->getToRemember()));;
    }

    public function getTerminalId(APIListRequest $request): ?int
    {
        $filters = $request->input('filters');
        $current = Currents::get($request);

        if ($current->isStaffAdmin() || $current->isStaffAccountant() || $current->isStaffPromoterManager()) {
            if ($request->has('terminal_id') && $request->input('terminal_id') !== null) {
                return $request->input('terminal_id');
            } else if (!empty($filters['terminal_id'])) {
                return $filters['terminal_id'];
            }
        } else if ($current->isStaffTerminal()) {
            return $current->terminalId();
        }

        return null;
    }

}
