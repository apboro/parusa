<?php

namespace App\Http\Controllers\API\Statistics;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\APIListRequest;
use App\Models\QrCodesStatistic;
use Carbon\Carbon;

class StatisticsQrCodesController extends Controller
{
    protected array $defaultFilters = [
        'date_from' => null,
        'date_to' => null,
        'partner_id' => null,
    ];

    protected array $rememberFilters = [
        'date_from',
        'date_to',
        'partner_id',
    ];

    protected string $rememberKey = CookieKeys::qr_codes_list;

    public function list(APIListRequest $request)
    {
        $this->defaultFilters['date_from'] = Carbon::now()->day(1)->format('Y-m-d');
        $this->defaultFilters['date_to'] = Carbon::now()->format('Y-m-d');
        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey);

        $query = QrCodesStatistic::query()->with('qr_code');

        // apply filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', Carbon::parse($filters['date_from']));
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', Carbon::parse($filters['date_to']));
        }
        if (!empty($filters['partner_id'])) {
            $query->whereHas('qr_code', function ($query) use ($filters) {
                $query->where('partner_id', $filters['partner_id']);
            });
        }
        $query->selectRaw("SUM(is_visit) as visits_count")
        ->selectRaw("SUM(is_payment) as payed_tickets_count")
        ->selectRaw("qr_code_id")
        ->groupBy('qr_code_id');


        $qrcodes = $query->paginate($request->perPage(10, $this->rememberKey));


        return APIResponse::list(
            $qrcodes,
            ['Название', 'Ссылка','Визиты','Покупки','QR-код']
        );
    }
}
