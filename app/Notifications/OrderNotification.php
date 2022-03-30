<?php

namespace App\Notifications;

use App\Classes\Mail\MailMessage;
use App\Helpers\OrderPdf;
use App\Helpers\TicketPdf;
use App\Models\Order\Order;
use Illuminate\Notifications\Notification;

class OrderNotification extends Notification
{
    /** @var Order $order */
    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
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
        $pdf = OrderPdf::a4($this->order);
        $message = (new MailMessage)
            ->subject("Заказ N{$this->order->id}")
            ->greeting('Благодарим за преобретение билетов. Вот они.')
            ->attachData($pdf, "Заказ N{$this->order->id}.pdf");

        // TODO filter tickets
        foreach ($this->order->tickets as $ticket) {
            $message->attachData(TicketPdf::a4($ticket), "Билет N{$ticket->id}.pdf");
        }

        return $message;
    }
}
