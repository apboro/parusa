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
        return self::generate($ticket, 'a4', 'portrait', 'pdf/ticket_a4');
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
        $size = [0, 0, 127.55, 240.94]; // 45 * 85

        return self::generate($ticket, $size, 'landscape', 'pdf/ticket_print');
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
        $view = View::make($template, ['ticket' => $ticket]);
        $html = $view->render();

        return Pdf::generate($html, $paperSize, $orientation);
    }
}
