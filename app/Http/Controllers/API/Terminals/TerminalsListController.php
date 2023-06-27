<?php

namespace App\Http\Controllers\API\Terminals;

use App\Helpers\PriceConverter;
use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\PaymentStatus;
use App\Models\Dictionaries\TerminalStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Hit\Hit;
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
        Hit::register(HitSource::admin);
        $periodHour = 4;
        $periodMinutes = 30;

        $now = Carbon::now();

        $periodStart = $now->clone()->hours($periodHour)->minutes($periodMinutes);
        if($periodStart->isPast()) {
            $periodEnd = $periodStart->clone()->addDay();
        } else {
            $periodEnd = $periodStart->clone();
            $periodStart->addDays(-1);
        }

        $query = Terminal::query()
            ->with(['status', 'pier', 'pier.info']);

        // apply filters
        if (!empty($filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey)) && !empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        // current page automatically resolved from request via `page` parameter
        $terminals = $query->paginate($request->perPage(10, $this->rememberKey));

        /** @var LengthAwarePaginator $terminals */
        $terminals->transform(function (Terminal $terminal) use ($now, $periodStart, $periodEnd) {
            $ticketsCount = Ticket::query()
                ->whereHas('order', function (Builder $query) use ($terminal) {
                    $query->where('terminal_id', $terminal->id);
                })
                ->where('updated_at', '>=', $periodStart)
                ->where('updated_at', '<=', $periodEnd)
                ->whereIn('status_id', TicketStatus::ticket_had_paid_statuses)
                ->count();

            $query = Payment::query()
                ->where('terminal_id', $terminal->id)
                ->where('created_at', '>=', $periodStart)
                ->where('created_at', '<=', $periodEnd);

            $saleQuery = $query->clone()->where('status_id', PaymentStatus::sale);
            $ticketsSoldAmount = $saleQuery->sum('total');
            $ticketsSoldCardAmount = $saleQuery->sum('by_card');
            $ticketsSoldCashAmount = $saleQuery->sum('by_cash');
            $returnQuery = $query->clone()->where('status_id', PaymentStatus::return);
            $ticketsReturnAmount = $returnQuery->sum('total');
            $ticketsReturnCardAmount = $returnQuery->sum('by_card');
            $ticketsReturnCashAmount = $returnQuery->sum('by_cash');

            $lastSale = Order::query()
                ->where('terminal_id', $terminal->id)
                ->whereIn('status_id', OrderStatus::order_had_paid_statuses)
                ->where('updated_at', '>=', $periodStart)
                ->where('updated_at', '<=', $periodEnd)
                ->max('updated_at');

            return [
                'active' => $terminal->hasStatus(TerminalStatus::enabled),
                'id' => $terminal->id,
                'status_id' => $terminal->status_id,
                'status' => $terminal->status->name,
                'pier' => $terminal->pier->name,
                'place' => $terminal->pier->info->address,
                'today_sold_amount' => PriceConverter::storeToPrice($ticketsSoldAmount),
                'today_sold_card_amount' => PriceConverter::storeToPrice($ticketsSoldCardAmount),
                'today_sold_cash_amount' => PriceConverter::storeToPrice($ticketsSoldCashAmount),
                'today_return_amount' => -PriceConverter::storeToPrice($ticketsReturnAmount),
                'today_return_card_amount' => -PriceConverter::storeToPrice($ticketsReturnCardAmount),
                'today_return_cash_amount' => -PriceConverter::storeToPrice($ticketsReturnCashAmount),
                'today_total_card_amount' => PriceConverter::storeToPrice($ticketsSoldCardAmount - $ticketsReturnCardAmount),
                'today_total_cash_amount' => PriceConverter::storeToPrice($ticketsSoldCashAmount - $ticketsReturnCashAmount),
                'today_total' => PriceConverter::storeToPrice($ticketsSoldAmount - $ticketsReturnAmount),
                'timestamp' => Carbon::now()->format('H:i, d.m.Y'),
                'today_tickets_sold' => $ticketsCount,
                'last_sale' => $lastSale ? Carbon::parse($lastSale)->format('H:i') : '—',
                'period_start' => $periodStart->format('H:i, d.m.Y'),
                'period_end' => $periodEnd->format('H:i, d.m.Y'),
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
