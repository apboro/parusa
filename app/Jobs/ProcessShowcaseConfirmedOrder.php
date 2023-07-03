<?php

namespace App\Jobs;

use App\Classes\EmailReceiver;
use App\LifePay\CloudPrint;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;
use App\NevaTravel\NevaOrder;
use App\Notifications\OrderNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ProcessShowcaseConfirmedOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $orderId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
        $this->connection = 'database';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var Order|null $order */
        $order = Order::query()
            ->with([
                    'tickets',
                    'payments' => function (HasMany $query) {
                        $query
                            ->where('gate', 'sber')
                            ->latest()
                            ->limit(1);
                    }]
            )
            ->where('id', $this->orderId)
            ->first();

        if ($order === null || !$order->hasStatus(OrderStatus::showcase_confirmed)) {
            return;
        }

        $tickets = [];

        // update order status
        $order->setStatus(OrderStatus::showcase_paid);
        $order->tickets->map(function (Ticket $ticket) use (&$tickets) {
            $ticket->setStatus(TicketStatus::showcase_paid);
            $tickets[] = $ticket;
        });

        try {
            (new NevaOrder($order))->approve();
        } catch (Exception $exception) {

        }

        if (env('SBER_ACQUIRING_PRODUCTION'))
        {
            CloudPrint::createReceipt($order, $tickets,CloudPrint::payment , $order->payments->first());
        }

        // send tickets
        try {
            Notification::sendNow(new EmailReceiver($order->email, $order->name), new OrderNotification($order));
        } catch (Exception $exception) {
            Log::channel('tickets_sending')->error(sprintf("Error order [%s] sending tickets [%s]: %s", $order->id, $order->email, $exception->getMessage()));
        }

        // pay commission
        try {
            $order->payCommissions();
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
