<?php

namespace App\Console\Commands;

use App\Events\AstraMarineCancelOrderEvent;
use App\Events\CityTourCancelOrderEvent;
use App\Events\NevaTravelCancelOrderEvent;
use App\Jobs\SendOrderEmailToGuideJob;
use App\Models\Dictionaries\ExcursionType;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;
use App\Services\NevaTravel\NevaOrder;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ProcessOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Destroy reserves and long delayed orders';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // OrderStatus::partner_reserve:
        $this->destroyPartnerReserves(now());
        $this->cancelShowcaseDelayed(now()->addHours(-1));
        $this->cancelApiDelayed(now()->subMinutes(15));
        $this->cancelYagaDelayed(now()->subMinutes(15));
        $this->cancelPromotersDelayed(now()->subMinutes(15));
        $this->sendEmailToGuides(now()->subMinute());

        return 0;
    }

    private function sendEmailToGuides(Carbon $date): void
    {
        $orders = Order::query()
            ->with(['tickets'])
            ->whereIn('status_id', OrderStatus::order_printable_statuses)
            ->where('updated_at', '>', $date)
            ->whereHas('tickets', function (Builder $query) use ($date) {
                $query->whereHas('trip', function (Builder $query) use ($date) {
                    $query->whereHas('excursion', function (Builder $query) use ($date) {
                        $query->where('type_id', ExcursionType::legs);
                    });
                });
            })
            ->get();

        foreach ($orders as $order) {
            SendOrderEmailToGuideJob::dispatch($order);
        }
    }

    /**
     * Destroy partner reserves.
     *
     * @param Carbon $now
     *
     * @return void
     */
    protected function destroyPartnerReserves(Carbon $now): void
    {
        $orders = Order::query()
            ->whereIn('status_id', [OrderStatus::partner_reserve])
            ->whereHas('tickets', function (Builder $query) use ($now) {
                $query->whereHas('trip', function (Builder $query) use ($now) {
                    $query->whereRaw('DATE_SUB(`start_at`, INTERVAL `cancellation_time` MINUTE) <= \'' . $now . '\'');
                });
            })
            ->with('tickets')
            ->get();

        foreach ($orders as $order) {
            /** @var Order $order */
            try {
                DB::transaction(static function () use ($order) {

                    NevaTravelCancelOrderEvent::dispatch($order);
                    CityTourCancelOrderEvent::dispatch($order);
                    AstraMarineCancelOrderEvent::dispatch($order);

                    $order->setStatus(OrderStatus::partner_reserve_canceled);
                    $order->tickets->map(function (Ticket $ticket) {
                        $ticket->setStatus(TicketStatus::partner_reserve_canceled);
                    });
                });
            } catch (Exception $exception) {
                Log::error('destroyPartnerReserves error', [$exception]);
            }
        }
        $this->info('All done.');
    }

    /**
     * Cancel showcase long delayed orders.
     *
     * @param Carbon $before
     *
     * @return void
     */
    protected function cancelShowcaseDelayed(Carbon $before): void
    {
        $orders = Order::query()
            ->with('tickets')
            ->whereIn('status_id', [OrderStatus::showcase_creating, OrderStatus::showcase_wait_for_pay, OrderStatus::partner_wait_for_pay])
            ->where('created_at', '<=', $before)
            ->get();

        foreach ($orders as $order) {
            /** @var Order $order */

            try {
                DB::transaction(static function () use ($order) {

                    $order->setStatus(OrderStatus::showcase_canceled);
                    $order->tickets->map(function (Ticket $ticket) {
                        $ticket->setStatus(TicketStatus::showcase_canceled);
                    });

                    AstraMarineCancelOrderEvent::dispatch($order);
                });
            } catch (Exception $exception) {
                Log::error('destroy showcase reserve error', [$exception]);
            }
        }
    }

    /**
     * Cancel API long delayed orders.
     *
     * @param Carbon $before
     *
     * @return void
     */
    protected function cancelApiDelayed(Carbon $reserveTime): void
    {
        $orders = Order::query()
            ->with('tickets')
            ->whereIn('status_id', [OrderStatus::api_reserved])
            ->where('created_at', '<=', $reserveTime)
            ->get();

        foreach ($orders as $order) {
            /** @var Order $order */
            try {
                DB::transaction(static function () use ($order) {

                    NevaTravelCancelOrderEvent::dispatch($order);
                    CityTourCancelOrderEvent::dispatch($order);
                    AstraMarineCancelOrderEvent::dispatch($order);

                    $order->setStatus(OrderStatus::api_canceled);
                    $order->tickets->map(function (Ticket $ticket) {
                        $ticket->setStatus(TicketStatus::api_canceled);
                    });
                });
            } catch (Exception $exception) {
                Log::error('destroy api reserve error', [$exception]);
            }
        }
    }

    protected function cancelYagaDelayed(Carbon $reserveTime): void
    {
        $orders = Order::query()
            ->with('tickets')
            ->whereIn('status_id', [OrderStatus::yaga_reserved])
            ->where('created_at', '<=', $reserveTime)
            ->get();

        foreach ($orders as $order) {
            /** @var Order $order */
            $order->setStatus(OrderStatus::yaga_canceled);
            $order->tickets->map(function (Ticket $ticket) {
                $ticket->setStatus(TicketStatus::yaga_canceled);
            });
        }
    }

    protected function cancelPromotersDelayed(Carbon $reserveTime): void
    {
        $orders = Order::query()
            ->with('tickets')
            ->whereIn('status_id', [OrderStatus::promoter_wait_for_pay])
            ->where('created_at', '<=', $reserveTime)
            ->get();

        foreach ($orders as $order) {
            /** @var Order $order */
            try {
                DB::transaction(static function () use ($order) {

                    NevaTravelCancelOrderEvent::dispatch($order);
                    CityTourCancelOrderEvent::dispatch($order);
                    AstraMarineCancelOrderEvent::dispatch($order);

                    $order->setStatus(OrderStatus::promoter_canceled);
                    $order->tickets->map(function (Ticket $ticket) {
                        $ticket->setStatus(TicketStatus::promoter_canceled);
                    });
                });
            } catch (Exception $exception) {
                Log::error('destroy promoter reserve error', [$exception]);
            }
        }
    }
}
