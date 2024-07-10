<?php

namespace App\Http\Controllers\API\Registries;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\AccountTransactionType;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\PartnerType;
use App\Models\Dictionaries\Provider;
use App\Models\Hit\Hit;
use App\Models\Partner\Partner;
use App\Models\User\Helpers\Currents;
use App\Models\WorkShift\WorkShift;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Models\Order\Order;
use Illuminate\Support\Collection;

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

        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey);

        $query = $this->getList($request);

        $partners = $query->paginate($request->perPage(25, $this->rememberKey));

        $partners->transform(function (Partner $partner) use ($filters) {

            $totalToPay = $partner->account->transactions->sum(function($transaction) {
                if ($transaction->type_id === AccountTransactionType::tickets_sell_commission) {
                    return $transaction->amount;
                } else if ($transaction->type_id === AccountTransactionType::tickets_sell_commission_return) {
                    return -$transaction->amount;
                }
            });

            return [
                'id' => $partner->id,
                'name' => $partner->name,
                'total_hours' => $partner->workShifts->sum(function ($shift) {
                    return $shift->getWorkingHours();
                }),
                'sales_total' => (int)$partner->orders->sum(function (Order $order) {
                    return $order->total();
                }),
                'commission_scarlet_sails' => $partner->account->transactions->sum(function ($transaction) {
                    if ($transaction->ticket?->provider_id === Provider::scarlet_sails) {
                        if ($transaction->type_id === AccountTransactionType::tickets_sell_commission) {
                            return $transaction->amount;
                        } else if ($transaction->type_id === AccountTransactionType::tickets_sell_commission_return) {
                            return -$transaction->amount;
                        }
                    }
                }),
                'commission_partners' => $partner->account->transactions->sum(function ($transaction) {
                    if ($transaction->ticket?->provider_id !== Provider::scarlet_sails) {
                        if ($transaction->type_id === AccountTransactionType::tickets_sell_commission) {
                            return $transaction->amount;
                        } else if ($transaction->type_id === AccountTransactionType::tickets_sell_commission_return) {
                            return -$transaction->amount;
                        }
                    }
                }),
                'total_to_pay_out' => $totalToPay,
                'total_paid_out' => $partner->workShifts->sum('paid_out'),
                'balance' => $totalToPay - $partner->workShifts->sum('paid_out'),
                'taxi' => $partner->workShifts->sum('taxi')
            ];
        });


        return APIResponse::list(
            $partners,
            ['ID', 'ФИО', 'Кол-во часов', 'Касса', '% от кассы, АП', '% от кассы, партнёры', 'Такси', 'Всего начислено', 'Получено', 'Долг'],
            $filters,
            $this->defaultFilters,
            [
                'total_to_pay_out' => $partners->sum('total_to_pay_out'),
                'total_paid_out' => $partners->sum('total_paid_out'),
                'total_earned' => $partners->sum('sales_total'),
            ])->withCookie(cookie($this->rememberKey, $request->getToRemember()));;
    }

    public
    function getTerminalId(APIListRequest $request): ?int
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

    public
    function export(ApiListRequest $request): JsonResponse
    {
        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey);

        $query = $this->getList($request);

        $partners = $query->get();

        $titles = [
            'ID',
            'ФИО',
            'КОЛ-ВО ЧАСОВ',
            'КАССА',
            '% от кассы, АП',
            '% от кассы, партнёры',
            'ВСЕГО НАЧИСЛЕНО',
            'ПОЛУЧЕНО',
            'ДОЛГ',
            'ТАКСИ',
        ];

        $partners->transform(function (Partner $partner) use ($filters) {

            return [
                'id' => $partner->id,
                'name' => $partner->name,
                'total_hours' => $partner->workShifts->sum(function ($shift) {
                    return $shift->getWorkingHours();
                }),
                'sales_total' => $partner->orders->sum(function (Order $order) {
                    return (int)$order->total();
                }),
                'commission_scarlet_sails' => $partner->account->transactions->sum(function ($transaction) {
                    if ($transaction->ticket?->provider_id === Provider::scarlet_sails) {
                        return $transaction->amount;
                    }
                }),
                'commission_partners' => $partner->account->transactions->sum(function ($transaction) {
                    if ($transaction->ticket?->provider_id !== Provider::scarlet_sails) {
                        return $transaction->amount;
                    }
                }),
                'total_to_pay_out' => $partner->account->transactions->sum('amount'),
                'total_paid_out' => $partner->workShifts->sum('paid_out'),
                'balance' => $partner->account->transactions->sum('amount') - $partner->workShifts->sum('paid_out') + $partner->getLastShift()?->balance,
                'taxi' => $partner->workShifts->sum('taxi')
            ];
        });

        $spreadsheet = new Spreadsheet();

        $spreadsheet->setActiveSheetIndex(0)->setTitle('Транзакции')->setShowRowColHeaders(true);

        $spreadsheet->getActiveSheet()->fromArray($titles, '—', 'A1');
        $spreadsheet->getActiveSheet()->fromArray($partners->toArray(), '—', 'A2');
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'] as $col) {
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

    private function getList($request)
    {
        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey);
        $terminalId = $this->getTerminalId($request);

        $query = Partner::query()
            ->with('workShifts', function ($query) use ($filters, $terminalId) {
                $query->where('start_at', '>=', $filters['date_from'])
                    ->where('start_at', '<=', $filters['date_to']);
                if ($terminalId) {
                    $query->where('terminal_id', $terminalId);
                }
            })
            ->with('orders', function (HasMany $query) use ($filters, $terminalId) {
                $query->with(['tickets', 'promocode'])
                    ->where('created_at', '>=', $filters['date_from'])
                    ->where('created_at', '<=', $filters['date_to'])
                    ->whereIn('status_id', OrderStatus::order_printable_statuses);
                if ($terminalId) {
                    $query->where('terminal_id', $terminalId);
                }
            })
            ->with('account.transactions', function ($query) use ($filters, $terminalId) {
                $query->with('ticket')
                    ->where('created_at', '>=', $filters['date_from'])
                    ->where('created_at', '<=', $filters['date_to']);
                if ($terminalId) {
                    $query->whereHas('ticket', function (Builder $query) use ($filters, $terminalId) {
                        $query->whereHas('order', function (Builder $query) use ($filters, $terminalId) {
                            $query->where('terminal_id', $terminalId);
                        });
                    });
                }
            })
            ->whereHas('workShifts', function ($q) use ($filters) {
                $q->where('start_at', '>=', $filters['date_from'])
                    ->where('start_at', '<=', $filters['date_to']);
            })
            ->where('type_id', PartnerType::promoter);

        if ($terminalId) {
            $query->whereHas('workShifts', fn($q) => $q->where('terminal_id', $terminalId))
                ->whereHas('orders', function (Builder $query) use ($filters, $terminalId) {
                    $query->where('terminal_id', $terminalId);
                });
        }
        if ($request->input('search')) {
            $query->where(function (Builder $query) use ($request) {
                $query->where('name', 'like', '%' . $request->input('search') . '%')
                    ->orWhere('id', 'like', $request->input('search') . '%');
            });
        }

        return $query;
    }

}
