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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class PromoterPayOutController extends ApiController
{
    protected array $defaultFilters = [
        'date_from' => null,
        'date_to' => null,
    ];

    protected array $rememberFilters = [
    ];

    public function list(APIListRequest $request): JsonResponse
    {
        $current = Currents::get($request);

        Hit::register(HitSource::admin);
        $this->defaultFilters['date_from'] = Carbon::now()->day(1)->format('Y-m-d');
        $this->defaultFilters['date_to'] = Carbon::now()->format('Y-m-d');
        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, '');

        $query = WorkShift::query()
            ->where('partner_id', $request->id)
            ->whereNotNull('paid_out')
            ->when($filters['date_from'], function ($query) use ($filters) {
                $query->whereDate('start_at', '>=', $filters['date_from']);
            })->when($request->date_to, function ($query) use ($filters) {
                $query->whereDate('end_at', '<=', $filters['date_to']);
            });

        $payOuts = $query->paginate($request->perPage(10, ''));

        $payOuts->transform(function (WorkShift $workShift) {
             return [
                 'start_at' =>$workShift->start_at->translatedFormat('D, d M Y'),
                 'paid_out' => $workShift->paid_out,
             ];
        });

        return APIResponse::list(
            $payOuts,
            [
                'start_at' => 'Дата',
                'pay_out' => 'Выплата',
            ],
            $filters,
            $this->defaultFilters,
            []
        );
    }

}
