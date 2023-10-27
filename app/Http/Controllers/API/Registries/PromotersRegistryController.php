<?php

namespace App\Http\Controllers\API\Registries;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\HitSource;
use App\Models\Hit\Hit;
use App\Models\User\Helpers\Currents;
use App\Models\WorkShift\WorkShift;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class PromotersRegistryController extends ApiController
{
    protected array $defaultFilters = [
        'date' => null,
        'terminal_id' => null,
    ];
    protected array $rememberFilters = [
    ];

    public function __construct()
    {
        $this->defaultFilters['date'] = Carbon::now()->startOfDay()->format('Y-m-d\TH:i');
    }

    protected string $rememberKey = CookieKeys::promoters_list;

    public function list(ApiListRequest $request): JsonResponse
    {
        Hit::register(HitSource::admin);

        $terminalId = $this->getTerminalId($request);

        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey);
        $query = WorkShift::query()
            ->with(['promoter', 'tariff'])
            ->whereDate('created_at', $filters['date']);

        if ($terminalId) {
            $query->where('terminal_id', $terminalId);
        }
        if ($request->input('search')) {
            $query->whereHas('promoter', fn($q) => $q->where('name', 'like', '%' . $request->input('search') . '%')
                ->orWhere('id', 'like', $request->input('search') . '%'));
        }

        $totalToPayout = 0;
        $totalPaidOut = $query->clone()->sum('paid_out');
        $totalToPayout += $query->get()->map(function ($shift) {
            return $shift->getShiftTotalPay();
        })->sum();
        $workShifts = $query->paginate($request->perPage(25, $this->rememberKey));

        $workShifts->transform(function ($shift){
            return [
                'id' => $shift->promoter->id,
                'name' => $shift->promoter->name,
                'start_at' => $shift->start_at,
                'end_at' => $shift->end_at,
                'pay_for_out' => $shift->tariff->pay_for_out,
                'working_hours' => $shift->getWorkingHours(),
                'tariff' => $shift->tariff->name,
                'pay_per_hour' => $shift->tariff->pay_per_hour,
                'pay_for_time' => $shift->getPayForTime(),
                'sales_total' => $shift->sales_total,
                'pay_commission' => $shift->pay_commission,
                'pay_total' => $shift->getShiftTotalPay(),
                'paid_out' => $shift->paid_out,
            ];
        });


        return APIResponse::list(
            $workShifts,
            ['ID', 'ФИО', 'Начало смены', 'Конец смены', 'За выход', 'Кол-во часов', 'Тариф', 'Почасовка', 'Оплата за время', 'Касса', '% от кассы', 'Всего начислено', 'Получено'],
            $filters,
            $this->defaultFilters,
            [
                'total_to_pay_out' => $totalToPayout,
                'total_paid_out' => $totalPaidOut
            ])->withCookie(cookie($this->rememberKey, $request->getToRemember()));;
    }

    public function getTerminalId(APIListRequest $request): ?int
    {
        $filters = $request->input('filters');
        $current = Currents::get($request);

        if ($current->isStaffAdmin() || $current->isStaffAccountant()) {
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
