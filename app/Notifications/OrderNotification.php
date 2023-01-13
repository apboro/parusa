<?php

namespace App\Notifications;

use App\Classes\Mail\MailMessage;
use App\Helpers\TicketPdf;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @var Order $order */
    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->connection = 'database';
    }

    /**
     * Get the notification's channels.
     *
     * @return  array
     */
    public function via(): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @return  MailMessage
     */
    public function toMail(): MailMessage
    {
        $message = new MailMessage();
        $message->subject("Заказ N{$this->order->id}");
        $text = explode("\n", Settings::get('buyer_email_welcome'));
        foreach ($text as $line) {
            $message->line($line);
        }

        $tickets = $this->order->tickets()->whereIn('status_id', TicketStatus::ticket_printable_statuses)->get();

        foreach ($tickets as $ticket) {
            $message->attachData(TicketPdf::a4($ticket), "Билет N{$ticket->id}.pdf");
        }

        return $message;
    }
}
