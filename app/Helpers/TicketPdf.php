<?php

namespace App\Helpers;

use App\Models\Tickets\Ticket;
use Illuminate\Support\Facades\View;

class TicketPdf
{
    /**
     * Ticket standard PDF.
     *
     * @param Ticket $ticket
     *
     * @return string|null
     */
    public static function a4(Ticket $ticket): ?string
    {
        $size = [0, 0, 595.28, 841.89]; // A4

        if ($ticket->order->neva_travel_order_number) {
            $view = 'pdf/ticket_a4_neva';
        } else {
            $view = env('PDF_TICKET_A4', 'pdf/ticket_a4');
        }

        return self::generate($ticket, $size, 'portrait', $view);
    }

    /**
     * Ticket printable form.
     *
     * @param Ticket $ticket
     *
     * @return string|null
     */
    public static function print(Ticket $ticket): ?string
    {
        $size = [0, 0, 226, 340];

        $view = env('PDF_TICKET_PRINT', 'pdf/ticket_print');

        return self::generate($ticket, $size, 'landscape', $view);
    }

    /**
     * Generate ticket PDF.
     *
     * @param Ticket $ticket
     * @param $paperSize
     * @param string $orientation
     * @param string $template
     *
     * @return  string|null
     */
    public static function generate(Ticket $ticket, $paperSize, string $orientation, string $template): ?string
    {
        $ticket->loadMissing('trip');
        $ticket->loadMissing('trip.startPier');
        $ticket->loadMissing('trip.startPier.info');
        $ticket->loadMissing('trip.excursion');
        $ticket->loadMissing('trip.excursion.info');
        $ticket->loadMissing('grade');

        $view = View::make($template, ['ticket' => $ticket]);
        $html = $view->render();

        return Pdf::generate($html, $paperSize, $orientation);
    }
}
