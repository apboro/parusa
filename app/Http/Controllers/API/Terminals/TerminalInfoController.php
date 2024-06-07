<?php

namespace App\Http\Controllers\API\Terminals;

use App\Helpers\PriceConverter;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Hit\Hit;
use App\Models\Order\Order;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TerminalInfoController extends ApiController
{
    public function info(Request $request): JsonResponse
    {
        Hit::register(HitSource::terminal);
        $current = Currents::get($request);
        $terminalId = $current->terminalId();

        if ($terminalId === null || $current->position() === null) {
            return APIResponse::notFound('Неверные параметры.');
        }

        $current->position()->ordering()
            ->whereHas('trip', fn($trip) => $trip->where('end_at', '<', now()))
            ->delete();

        /** @var Order $processing */
        $processing = Order::query()->with(['status'])
            ->where(['position_id' => $current->positionId(), 'terminal_id' => $terminalId])
            ->whereIn('status_id', OrderStatus::terminal_processing_statuses)
            ->first();

        if($processing) {
            $orderAmount = $processing->total();
        } else {
            $tickets = $current->position()->ordering()
                ->where('terminal_id', $terminalId)
                ->leftJoin('trips', 'trips.id', '=', 'position_ordering_tickets.trip_id')
                ->where('trips.end_at', '>', Carbon::now())
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
                })->get(['position_ordering_tickets.id as pot_id', 'parent_ticket_id', 'backward_price_value', 'quantity', 'position_ordering_tickets.base_price']);

            $orderAmount = 0;
            $tickets->map(function ($ticket) use ($tickets, &$orderAmount){
                if ($ticket->parent_ticket_id){
                    $parent_ticket = $tickets->where('pot_id',$ticket->parent_ticket_id)->first();
                    $orderAmount += $parent_ticket->backward_price_value * $ticket->quantity;
                } else {
                    $orderAmount += $ticket->quantity * $ticket->base_price;
                }
            });
        }

        return APIResponse::response([
            'order_amount' => $orderAmount ?? 0,
            'current' => $processing ? $processing->status->name : 'Создание заказа',
        ]);
    }
}
