<?php

namespace App\Helpers;

use App\Models\Tickets\Order;
use Illuminate\Support\Facades\View;

class OrderPdf
{
    /**
     * Ticket standard PDF.
     *
     * @param Order $order
     *
     * @return string|null
     */
    public static function a4(Order $order): ?string
    {
        return self::generate($order, 'a4', 'portrait', 'pdf/order_a4');
    }

    /**
     * Ticket printable form.
     *
     * @param Order $order
     *
     * @return string|null
     */
    public static function print(Order $order): ?string
    {
        $size = [0, 0, 127.55, 240.94]; // 45 * 85

        return self::generate($order, $size, 'landscape', 'pdf/order_print');
    }

    /**
     * Generate ticket PDF.
     *
     * @param Order $order
     * @param $paperSize
     * @param string $orientation
     * @param string $template
     *
     * @return  string|null
     */
    public static function generate(Order $order, $paperSize, string $orientation, string $template): ?string
    {
        $view = View::make($template, ['order' => $order]);
        $html = $view->render();

        return Pdf::generate($html, $paperSize, $orientation);
    }
}
