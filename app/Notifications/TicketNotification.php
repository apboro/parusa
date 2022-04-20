<?php

namespace App\Notifications;

use App\Classes\Mail\MailMessage;
use App\Helpers\TicketPdf;
use App\Models\Tickets\Ticket;
use App\Settings;
use Illuminate\Notifications\Notification;

class TicketNotification extends Notification
{
    /** @var Ticket $ticket */
    public Ticket $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
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
        $pdf = TicketPdf::a4($this->ticket);

        $message = new MailMessage;
        $message->subject("Билет N{$this->ticket->id}");
        $text = explode("\n", Settings::get('buyer_email_welcome'));
        foreach ($text as $line) {
            $message->line($line);
        }
        $message->attachData($pdf, "Билет N{$this->ticket->id}.pdf");

        return $message;
    }
}
