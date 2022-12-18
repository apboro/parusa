<?php

namespace App\Http\Controllers\API\Statistics;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class StatisticsSalesController extends ApiController
{
    protected array $defaultFilters = [
        'date_from' => null,
        'date_to' => null,
    ];

    protected array $rememberFilters = [
        'date_from',
        'date_to'
    ];

    protected string $rememberKey = CookieKeys::statistics_sales_list;

    /**
     * Get orders list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        $this->defaultFilters['date_from'] = Carbon::now()->day(1)->format('Y-m-d');
        $this->defaultFilters['date_to'] = Carbon::now()->format('Y-m-d');
        $filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey);

        $query = Order::query()
            ->with(['tickets', 'tickets.trip.excursion'])
            ->whereIn('status_id', OrderStatus::order_had_paid_statuses)
            ->where('type_id', OrderType::site);

        // apply filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', Carbon::parse($filters['date_from']));
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', Carbon::parse($filters['date_to']));
        }

        $orders = $query->get();

        $tickets_array = [];
        foreach ($orders as $order) {
            foreach ($order->tickets as $ticket) {
                $order_array['excursion_id'] = $ticket->trip->excursion->id;
                $order_array['base_price'] = $ticket->base_price;
                $order_array['excursion_name'] = $ticket->trip->excursion->name;
                $order_array['status_id'] = $ticket->status_id;
                $tickets_array[] = $order_array;
            }
        }

        $result=[];
        $sum_plus=0;
        $sum_minus=0;
        foreach ($tickets_array as $ticket) {
            $result[$ticket['excursion_id']]['name'] = $ticket['excursion_name'];

            if ($ticket['status_id'] != TicketStatus::showcase_returned) {
                if (isset($result[$ticket['excursion_id']]) and key_exists('total_sold', $result[$ticket['excursion_id']])) {
                    $result[$ticket['excursion_id']]['total_sold'] += $ticket['base_price'];
                } else {
                    $result[$ticket['excursion_id']]['total_sold'] = $ticket['base_price'];
                }
                $sum_plus += $ticket['base_price'];
            } else {
                if (isset($result[$ticket['excursion_id']]) and key_exists('total_returns', $result[$ticket['excursion_id']])) {
                    $result[$ticket['excursion_id']]['total_returns'] += $ticket['base_price'];
                } else {
                    $result[$ticket['excursion_id']]['total_returns'] = $ticket['base_price'];
                }
                $sum_minus +=$ticket['base_price'];
            }
            if (!key_exists('total_returns', $result[$ticket['excursion_id']]))
            {
                $result[$ticket['excursion_id']]['total_returns'] = 0;
            }
            $result['totals'] = [
                'total_plus' =>$sum_plus,
                'total_minus' =>$sum_minus,
            ];
        }

        return APIResponse::list(
            new LengthAwarePaginator($result,100,999),
            $filters,
            $this->defaultFilters,
            []
        );
    }

}
