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
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

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
        $query = Partner::query()->with(['workShifts' => function ($query) use ($filters) {
            $query->where(function ($q) use ($filters) {
                $q->whereDate('start_at', '>=', Carbon::parse($filters['date_from']))
                    ->whereDate('start_at', '<=', Carbon::parse($filters['date_to']));
            })->orWhere(function ($q) use ($filters) {
                $q->whereDate('end_at', '>=', Carbon::parse($filters['date_from']))
                    ->whereDate('end_at', '<=', Carbon::parse($filters['date_to']));
            });
        }])->where('type_id', PartnerType::promoter);

        if ($terminalId) {
            $query->whereHas('workShifts', fn($q) => $q->where('terminal_id', $terminalId));
        }
        if ($request->input('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%')
                ->orWhere('id', 'like', $request->input('search') . '%');
        }
        if (!empty($filters['date_from'])) {
            $query->whereHas('workShifts', function ($query) use ($filters) {
                $query->whereDate('start_at', '>=', Carbon::parse($filters['date_from']))->orWhereDate('end_at', '=', Carbon::parse($filters['date_to']));
            });
        }
        if (!empty($filters['date_to'])) {
            $query->whereHas('workShifts', function ($query) use ($filters) {
                $query->whereDate('end_at', '<=', Carbon::parse($filters['date_to']))->orWhereDate('start_at', '=', Carbon::parse($filters['date_to']));
            });
        }

        $partners = $query->paginate($request->perPage(25, $this->rememberKey));

        $partners->transform(function (Partner $partner) use ($filters) {

            $filteredWorkshifts = $partner->workShifts;
            $totalToPay = $filteredWorkshifts->sum(function ($shift) {
                return $shift->getShiftTotalPay();
            });

            return [
                'id' => $partner->id,
                'name' => $partner->name,
                'pay_for_out' => $filteredWorkshifts->sum('pay_for_out'),
                'total_hours' => $filteredWorkshifts->sum(function ($shift) {
                    return $shift->getWorkingHours();
                }),
                'pay_for_time' => $filteredWorkshifts->sum(function ($shift) {
                    return $shift->getPayForTime();
                }),
                'sales_total' => $filteredWorkshifts->sum('sales_total'),
                'commission' => $filteredWorkshifts->sum('pay_commission'),
                'total_to_pay_out' => $totalToPay,
                'total_paid_out' => $filteredWorkshifts->sum('paid_out'),
                'balance' => $totalToPay - $filteredWorkshifts->sum('paid_out'),
                'taxi' => $filteredWorkshifts->sum('taxi')
            ];
        });


        return APIResponse::list(
            $partners,
            ['ID', 'ФИО', 'За выход', 'Кол-во часов', 'Оплата за время', 'Касса', '% от кассы', 'Такси', 'Всего начислено', 'Получено', 'Долг'],
            $filters,
            $this->defaultFilters,
            [
                'total_to_pay_out' => $partners->sum('total_to_pay_out'),
                'total_paid_out' => $partners->sum('total_paid_out'),
                'total_earned' => $partners->sum('sales_total'),
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

    public function export(ApiListRequest $request): JsonResponse
    {
        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey);
        $terminalId = $this->getTerminalId($request);
        $query = Partner::query()
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

        $partners = $query->get();

        $titles = [
            'ID',
            'ФИО',
            'ЗА ВЫХОД',
            'КОЛ-ВО ЧАСОВ',
            'ОПЛАТА ЗА ВРЕМЯ',
            'КАССА',
            '% ОТ КАССЫ',
            'ВСЕГО НАЧИСЛЕНО',
            'ПОЛУЧЕНО',
            'ДОЛГ',
            'ТАКСИ',
        ];

        $partners->transform(function (Partner $partner) use ($filters) {

            $filteredWorkshifts = $partner->workShifts->filter(function ($shift) use ($filters) {
                if (!empty($filters['date_to']))
                    if (Carbon::parse($shift->start_at) > Carbon::parse($filters['date_to'])->endOfDay())
                        return false;
                if (!empty($filters['date_from']))
                    if (Carbon::parse($shift->start_at) < Carbon::parse($filters['date_from']))
                        return false;
                if (!empty($filters['terminal_id']))
                    if ($shift->terminal_id != $filters['terminal_id'])
                        return false;
                return true;
            });

            return [
                'id' => $partner->id,
                'name' => $partner->name,
                'pay_for_out' => $filteredWorkshifts->sum('pay_for_out'),
                'total_hours' => $filteredWorkshifts->sum(function ($shift) {
                    return $shift->getWorkingHours();
                }),
                'pay_for_time' => $filteredWorkshifts->sum(function ($shift) {
                    return $shift->getPayForTime();
                }),
                'sales_total' => $filteredWorkshifts->sum('sales_total'),
                'commission' => $filteredWorkshifts->sum('pay_commission'),
                'total_to_pay_out' => $filteredWorkshifts->sum(function ($shift) {
                    return $shift->getShiftTotalPay();
                }),
                'total_paid_out' => $filteredWorkshifts->sum('paid_out'),
                'balance' => $filteredWorkshifts->last()?->balance,
                'taxi' => $filteredWorkshifts->sum('taxi')
            ];
        });

        $spreadsheet = new Spreadsheet();

        $spreadsheet->setActiveSheetIndex(0)->setTitle('Транзакции')->setShowRowColHeaders(true);

        $spreadsheet->getActiveSheet()->fromArray($titles, '—', 'A1');
        $spreadsheet->getActiveSheet()->fromArray($partners->toArray(), '—', 'A2');
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'] as $col) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        ob_start();
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        $export = ob_get_clean();

        return APIResponse::response([
            'file' => base64_encode($export),
            'file_name' => 'Промоутеры ' . Carbon::now()->format('Y-m-d H:i'),
            'type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

}
