<?php

namespace App\Http\Controllers\API\Promoters;

use App\Helpers\PriceConverter;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\HitSource;
use App\Models\Hit\Hit;
use App\Models\Partner\Partner;
use App\Models\User\Helpers\Currents;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromoterInfoController extends ApiController
{
    public function get(Request $request): JsonResponse
    {
        Hit::register(HitSource::partner);
        $current = Currents::get($request);
        $id = $current->partnerId();

        if ($id === null ||
            null === ($partner = Partner::where('id', $id)->first())
        ) {
            return APIResponse::notFound('Партнёр не найден');
        }

        /** @var Partner $partner */
        if ($current->position() === null) {
            $orderAmount = 0;
        } else {
            $tickets = $current->position()->ordering()
                ->where('terminal_id', null)
                ->leftJoin('trips', 'trips.id', '=', 'position_ordering_tickets.trip_id')
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
                })->get(['position_ordering_tickets.id as pot_id', 'parent_ticket_id', 'backward_price_value', 'quantity', 'base_price', 'partner_price']);

            $orderAmount = 0;
            $tickets->map(function ($ticket) use ($tickets, &$orderAmount){
                if ($ticket->parent_ticket_id){
                    $parent_ticket = $tickets->where('pot_id',$ticket->parent_ticket_id)->first();
                    $orderAmount += $parent_ticket->backward_price_value * $ticket->quantity;
                } else {
                    $orderAmount += $ticket->quantity * $ticket->partner_price ?? $ticket->base_price;
                }
            });
        }
        $openshift = $partner->getOpenedShift();
        return APIResponse::response([
            'total' => $openshift ? $openshift->getShiftTotalPay() : null,
            'tariff' => $openshift? $openshift->getCurrentCommission() : null,
            'order_amount' => PriceConverter::storeToPrice($orderAmount ?? 0),
        ]);
    }
}
