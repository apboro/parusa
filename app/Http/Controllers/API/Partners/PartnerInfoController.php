<?php

namespace App\Http\Controllers\API\Partners;

use App\Helpers\PriceConverter;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Hit\Hit;
use App\Models\Order\Order;
use App\Models\Partner\Partner;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartnerInfoController extends ApiController
{
    public function get(Request $request): JsonResponse
    {
        Hit::register(HitSource::partner);
        $current = Currents::get($request);
        $id = $current->partnerId();

        if ($id === null ||
            null === ($partner = Partner::query()->with(['account'])->where('id', $id)->first())
        ) {
            return APIResponse::notFound('Партнёр не найден');
        }

        /** @var Partner $partner */
        if ($current->position() === null) {
            $orderAmount = 0;
        } else {
            $orderAmount = $current->position()->ordering()
                ->where('terminal_id', null)
                ->leftJoin('trips', 'trips.id', '=', 'position_ordering_tickets.trip_id')
                ->where('trips.start_at', '>', Carbon::now())
                ->leftJoin('excursions', 'excursions.id', '=', 'trips.excursion_id')
                ->leftJoin('tickets_rates_list', function (JoinClause $join) {
                    $join
                        ->on('tickets_rates_list.excursion_id', '=', 'excursions.id')
                        ->on(DB::raw('date(tickets_rates_list.start_at)'), '<=', DB::raw('date(trips.start_at)'))
                        ->on(DB::raw('date(tickets_rates_list.end_at)'), '>=', DB::raw('date(trips.start_at)'));
                })
                ->leftJoin('ticket_rates', function (JoinClause $join) {
                    $join->on('ticket_rates.rate_id', '=', 'tickets_rates_list.id')
                        ->on('ticket_rates.grade_id', '=', 'position_ordering_tickets.grade_id');
                })
                ->select(DB::raw('sum(position_ordering_tickets.quantity * ticket_rates.base_price) as total'))
                ->value('total');
            $orderAmount = PriceConverter::storeToPrice($orderAmount ?? 0);
        }

        $account = $partner->account;

        $fromDate = Carbon::now()->startOfYear()->hour(0)->minute(0);
        $toDate = Carbon::now()->startOfYear()->addYear()->hour(23)->minute(59)->second(59);
        $total = $account->calcAmount($toDate, $fromDate, null);

        return APIResponse::response([
            'amount' => $partner->account->amount,
            'limit' => $partner->account->limit,
            'total' => $total,
            'reserves' => Order::query()->where(['partner_id' => $partner->id, 'status_id' => OrderStatus::partner_reserve])->count(),
            'order_amount' => $orderAmount,
            'can_reserve' => $current->partner()->profile->can_reserve_tickets,
        ]);
    }
}
