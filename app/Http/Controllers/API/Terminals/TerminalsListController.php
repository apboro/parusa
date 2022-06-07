<?php

namespace App\Http\Controllers\API\Terminals;

use App\Helpers\PriceConverter;
use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\PaymentStatus;
use App\Models\Dictionaries\TerminalStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Payments\Payment;
use App\Models\POS\Terminal;
use App\Models\Tickets\Ticket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class TerminalsListController extends ApiController
{
    protected array $defaultFilters = [
        'status_id' => TerminalStatus::enabled,
    ];

    protected array $rememberFilters = [
        'status_id',
    ];

    protected string $rememberKey = CookieKeys::terminals_list;

    /**
     * Get terminals list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        $query = Terminal::query()
            ->with(['status', 'pier', 'pier.info']);

        // apply filters
        if (!empty($filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey)) && !empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        // current page automatically resolved from request via `page` parameter
        $terminals = $query->paginate($request->perPage(10, $this->rememberKey));

        $now = Carbon::now();

        /** @var LengthAwarePaginator $terminals */
        $terminals->transform(function (Terminal $terminal) use ($now) {
            $ticketsCount = Ticket::query()
                ->whereHas('order', function (Builder $query) use ($terminal) {
                    $query->where('terminal_id', $terminal->id);
                })
                ->whereDate('updated_at', $now)
                ->whereIn('status_id', TicketStatus::ticket_had_paid_statuses)
                ->count();

            $countQuery = Payment::query()->where('terminal_id', $terminal->id)->whereDate('created_at', $now)->where('status_id', PaymentStatus::sale);
            $ticketsAmount = $countQuery->sum('total');
            $ticketsCardAmount = $countQuery->sum('by_card');
            $ticketsCashAmount = $countQuery->sum('by_cash');

            $lastSale = Order::query()
                ->where('terminal_id', $terminal->id)
                ->whereDate('updated_at', $now)
                ->whereIn('status_id', OrderStatus::order_had_paid_statuses)
                ->max('updated_at');

            return [
                'active' => $terminal->hasStatus(TerminalStatus::enabled),
                'id' => $terminal->id,
                'status_id' => $terminal->status_id,
                'status' => $terminal->status->name,
                'pier' => $terminal->pier->name,
                'place' => $terminal->pier->info->address,
                'today_sold_amount' => PriceConverter::storeToPrice($ticketsAmount),
                'today_sold_card_amount' => PriceConverter::storeToPrice($ticketsCardAmount),
                'today_sold_cash_amount' => PriceConverter::storeToPrice($ticketsCashAmount),
                'today_tickets_sold' => $ticketsCount,
                'last_sale' => $lastSale ? Carbon::parse($lastSale)->format('H:i') : '—',
            ];
        });

        return APIResponse::list(
            $terminals,
            ['Касса', 'Статус', 'Причал', 'Адрес', 'Выручка за сегодня', 'Продано билетов', 'Последняя продажа'],
            $filters,
            $this->defaultFilters,
            []
        )->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }
}
