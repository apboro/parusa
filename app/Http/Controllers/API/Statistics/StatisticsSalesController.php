<?php

namespace App\Http\Controllers\API\Statistics;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Hit\Hit;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;
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
        'date_to',
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
        Hit::register(HitSource::admin);
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

        $result = [];

        $soldAmountTotal = 0;
        $returnAmountTotal = 0;

        foreach ($orders as $order) {
            /** @var Order $order */
            foreach ($order->tickets as $ticket) {
                /** @var Ticket $ticket */

                $soldAmount = in_array($ticket->status_id, TicketStatus::ticket_paid_statuses) ? $ticket->getPrice() : 0;
                $soldAmountTotal += $soldAmount;

                $returnedAmount = $ticket->status_id === TicketStatus::showcase_returned ? $ticket->getPrice() : 0;
                $returnAmountTotal += $returnedAmount;

                if (!isset($result[$ticket->trip->excursion_id])) {
                    $result[$ticket->trip->excursion_id] = [
                        'name' => $ticket->trip->excursion->name,
                        'sold_amount' => $soldAmount,
                        'returned_amount' => $returnedAmount,
                    ];
                } else {
                    $result[$ticket->trip->excursion_id]['sold_amount'] += $soldAmount;
                    $result[$ticket->trip->excursion_id]['returned_amount'] += $returnedAmount;
                }

            }
        }

        return APIResponse::list(
            new LengthAwarePaginator($result, 100, 999),
            null,
            $filters,
            $this->defaultFilters,
            [
                'sold_amount_total' => round($soldAmountTotal),
                'return_amount_total' => round($returnAmountTotal),
            ]
        );
    }

}
