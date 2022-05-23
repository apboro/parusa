<?php

use App\Models\Order\Order;
use App\Models\Tickets\Ticket;

/** @var Order $order */
/** @var Ticket[] $tickets */
?>

    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="margin: 0; padding: 0; width: 340pt;">
<head>
    <meta charset="utf-8">
    <title>Заказ N{{ $order->id }}</title>
    <style>
        @page {
            size: 340pt 226pt;
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body style="margin: 0; padding: 0; width: 340.15pt;">

@foreach($tickets as $ticket)
    <?php /** @var $loop */ ?>
    <div style="width: 340pt;margin: 7pt 0 0; page-break-inside: avoid;<?php echo !$loop->last ? 'page-break-after: always;' : ''; ?>">
        <div style="width: 315pt; height: 90pt; margin: 0 auto; border-bottom: 1px solid #5e5e5e; font-size: 0">
            <div style="display: inline-block; vertical-align: top; width: 170pt;">
                <h1 style="font-family:'Reforma Grotesk',serif;font-size: 34pt;margin: 0;text-transform: uppercase;line-height: 36pt;">Посадочный билет</h1>
            </div>
            <div style="display: inline-block;text-align: right; vertical-align: top; width: 145pt">
                <div style="margin: 0;font-family: 'Proxima Nova',serif;font-size: 7pt;line-height: 7pt;">
                    Адрес причала:
                </div>
                <div style="margin: 0;font-family: 'Proxima Nova',serif;font-size: 7pt;line-height: 7pt;">
                    <i>{{ $ticket->trip->startPier->info->address }}</i>
                </div>
                <div style="text-align: right">
                    <img src="{{ $ticket->trip->startPier->info->mapLinkQr() }}" alt="qr-link" style="width: 55pt; height: 55pt;margin: 5pt 0 0;">
                </div>
            </div>
        </div>

        <div style="width: 315pt; margin: 0 auto; font-size: 0">
            <div style="width: 100pt; display:inline-block; vertical-align: top;padding-top: 10pt;">
                <img src="{{ $ticket->qr() }}" alt="qr-link" style="width: 75pt; height: 75pt;">
                <div style="margin: 5pt 0 3pt;font-family: 'Proxima Nova',serif;font-size: 8pt;line-height: 7pt;">№ заказа {{ $ticket->order_id }}</div>
                <div style="margin: 0;font-family: 'Proxima Nova',serif;font-size: 8pt;line-height: 7pt;">№ билета {{ $ticket->id }}</div>
            </div>
            <div style="display:inline-block; vertical-align: top; padding-top: 5pt; width: 215pt;">
                <div style="width: 100%; font-family: 'Proxima Nova',serif;font-size: 7pt;">
                    <div style="white-space: nowrap; padding-top: 5pt;">
                        <div style="display: inline-block; width: 65pt;vertical-align: top; padding-right: 3pt;">Экскурсия</div>
                        <div
                            style="display: inline-block; vertical-align: top; width: 145pt; white-space: normal; border-bottom: 1px solid #5e5e5e;">{{ $ticket->trip->excursion->name }}</div>
                    </div>
                    <div style="white-space: nowrap; padding-top: 5pt;">
                        <div style="display: inline-block; width: 65pt; vertical-align: top; padding-right: 3pt;">Телефон причала</div>
                        <div
                            style="display: inline-block; vertical-align: top; width: 145pt; white-space: normal; border-bottom: 1px solid #5e5e5e;">{{ $ticket->trip->startPier->info->phone }}</div>
                    </div>
                    <div style="white-space: nowrap; padding-top: 5pt;">
                        <div style="display: inline-block; width: 65pt; vertical-align: top; padding-right: 3pt;">Дата поездки</div>
                        <div
                            style="display: inline-block; vertical-align: top; width: 145pt; white-space: normal; border-bottom: 1px solid #5e5e5e;">{{ $ticket->trip->start_at->format('d.m.Y,  H:i') }}</div>
                    </div>
                    <div style="white-space: nowrap; padding-top: 5pt;">
                        <div style="display: inline-block; width: 65pt; vertical-align: top; padding-right: 3pt;">Тип билета</div>
                        <div
                            style="display: inline-block; vertical-align: top; width: 145pt; white-space: normal; border-bottom: 1px solid #5e5e5e;">{{ $ticket->grade->name }}</div>
                    </div>
                    <div style="white-space: nowrap; padding-top: 5pt;">
                        <div style="display: inline-block; width: 65pt; vertical-align: top; padding-right: 3pt;">Стоимость</div>
                        <div style="display: inline-block; vertical-align: top; width: 145pt; white-space: normal; border-bottom: 1px solid #5e5e5e;">{{ $ticket->base_price }}
                            рублей
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach
</body>
</html>
