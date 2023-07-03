<?php

namespace App\Http\Controllers\API\Statistics;

use App\Helpers\PriceConverter;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
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

class StatisticsSalesTodayController extends ApiController
{
    /**
     * Get terminals list.
     *
     *
     * @return  JsonResponse
     */
    public function list(): JsonResponse
    {
        Hit::register(HitSource::admin);
        $periodHour = 4;
        $periodMinutes = 30;

        $now = Carbon::now();

        $periodStart = $now->clone()->hours($periodHour)->minutes($periodMinutes);
        if ($periodStart->isPast()) {
            $periodEnd = $periodStart->clone()->addDay();
        } else {
            $periodEnd = $periodStart->clone();
            $periodStart->addDays(-1);
        }

        return APIResponse::response([
            'terminals' => $this->getTerminalStats($periodStart, $periodEnd),
            'site' => $this->getSiteStats($periodStart, $periodEnd),
        ]);
    }

    protected function getTerminalStats($periodStart, $periodEnd)
    {
        $terminals = Terminal::query()
            ->with(['status', 'pier', 'pier.info'])
            ->get();

        $terminals->transform(function (Terminal $terminal) use ($periodStart, $periodEnd) {
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

        return $terminals;
    }

    protected function getSiteStats($periodStart, $periodEnd)
    {
        $siteSalesToday = Order::query()
            ->where('type_id', OrderType::site)
            ->where('created_at', '>=', $periodStart)
            ->where('created_at', '<=', $periodEnd);

        $paidSiteSalesToday = $siteSalesToday->clone()
            ->where('status_id', OrderStatus::showcase_paid)
            ->get();

        $returnedSiteSalesToday = $siteSalesToday->clone()
            ->whereIn('status_id', [OrderStatus::showcase_returned, OrderStatus::showcase_partial_returned])
            ->get();
        $lastSiteSaleToday = $siteSalesToday->clone()->max('updated_at');

        $sumPaidSiteSalesToday = 0;
        $totalTicketsToday = 0;
        $paidSiteSalesToday->map(function (Order $order) use (&$sumPaidSiteSalesToday, &$totalTicketsToday) {
            $order->tickets->map(function (Ticket $ticket) use (&$sumPaidSiteSalesToday, &$totalTicketsToday) {
                $sumPaidSiteSalesToday += $ticket->base_price;
                $totalTicketsToday += 1;
            });
        });

        $sumReturnedSalesToday = 0;
        $returnedSiteSalesToday->map(function (Order $order) use (&$sumReturnedSalesToday) {
            $order->tickets->map(function (Ticket $ticket) use (&$sumReturnedSalesToday) {
                if ($ticket->status_id === TicketStatus::showcase_returned) {
                    $sumReturnedSalesToday += $ticket->base_price;
                }
            });
        });

        return
            [
                'today_sold_amount' => $sumPaidSiteSalesToday,
                'today_return_amount' => $sumReturnedSalesToday,
                'today_tickets_sold' => $totalTicketsToday,
                'last_sale' => $lastSiteSaleToday ? Carbon::parse($lastSiteSaleToday)->format('H:i') : '—',
                'period_start' => $periodStart->format('H:i, d.m.Y'),
                'period_end' => $periodEnd->format('H:i, d.m.Y'),
            ];

    }
}
