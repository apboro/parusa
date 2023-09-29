<?php

namespace App\Helpers;

use App\Events\CityTourOrderPaidEvent;
use App\Events\NevaTravelOrderPaidEvent;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;
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
        NevaTravelOrderPaidEvent::dispatch($order);
        CityTourOrderPaidEvent::dispatch($order);

        if ($order->additionalData?->provider_id == Provider::city_tour) {
            $view = config('tickets.order_template_city_tour');
        } elseif ($order->tickets[0]->trip->excursion->is_single_ticket && $order->additionalData?->provider_id === null) {
            $view = config('tickets.order_template_single');
        } else {
            $view = config('tickets.order_template');
        }

        return self::generate($order, 'a4', 'portrait', $view);
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
        $size = [0, 0, 226, 340];

        NevaTravelOrderPaidEvent::dispatch($order);
        CityTourOrderPaidEvent::dispatch($order);

        if ($order->additionalData?->provider_id == Provider::city_tour) {
            $view = config('tickets.order_print_city_tour');
        } elseif ($order->tickets[0]->trip->excursion->is_single_ticket && $order->additionalData?->provider_id === null) {
            $view = config('tickets.order_print_single');
        } else {
            $view = config('tickets.order_print');
        }
        return self::generate($order, $size, 'portrait', $view);
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
        $tickets = $order->tickets()->whereIn('status_id', TicketStatus::ticket_printable_statuses)->get();

        $view = View::make($template, ['order' => $order, 'tickets' => $tickets]);
        $html = $view->render();

        return Pdf::generate($html, $paperSize, $orientation);
    }
}
