<?php

namespace App\Helpers;

use App\Events\CityTourOrderPaidEvent;
use App\Events\NevaTravelOrderPaidEvent;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;
use App\Models\WorkShift\WorkShift;
use Illuminate\Support\Facades\View;

class WorkShiftPdf
{
    /**
     * Ticket standard PDF.
     *
     * @param Order $order
     *
     * @return string|null
     */
    public static function a4(WorkShift $workShift): ?string
    {
        $view = 'pdf/work_shifts/printPayout';
        return self::generate($workShift, 'a4', 'portrait', $view);
    }

    /**
     * Ticket printable form.
     *
     * @param Order $workShift
     *
     * @return string|null
     */
    public static function print(WorkShift $workShift): ?string
    {
        $size = [0, 0, 226, 340];
        $view = 'pdf/work_shifts/print_payout';
        return self::generate($workShift, $size, 'portrait', $view);
    }

    /**
     * Generate ticket PDF.
     *
     * @param Order $workShift
     * @param $paperSize
     * @param string $orientation
     * @param string $template
     *
     * @return  string|null
     */
    public static function generate(WorkShift $workShift, $paperSize, string $orientation, string $template): ?string
    {
        $view = View::make($template, ['workShift' => $workShift]);
        $html = $view->render();

        return Pdf::generate($html, $paperSize, $orientation);
    }
}
