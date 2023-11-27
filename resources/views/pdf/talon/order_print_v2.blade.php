<!DOCTYPE html>
<?php

use App\Models\Order\Order;
use App\Models\Tickets\Ticket;

/** @var Order $order */
/** @var Ticket[] $tickets */
?>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="margin: 0; padding: 0; width: 226pt;">
<head>
    <meta charset="utf-8">
    <title>Заказ N{{ $order->id }}</title>
    <style>
        @page {
            size: 226pt 340pt;
            margin: 0;
            padding: 0;
        }
        /** {*/
        /*    outline: 1px solid rgba(255, 0, 242, 0.49);*/
        /*}*/
    </style>
</head>

<body style="box-sizing: border-box; margin: 0; padding: 0.5pt; width: 226pt;">

@foreach($tickets as $ticket)
    <?php /** @var $loop */ ?>
    <div style="left: 1pt; top: 1pt;position: relative; overflow: hidden; width: 220pt; height: 335pt; margin: 0; page-break-inside: avoid;<?php echo !$loop->last ? 'page-break-after: always;' : ''; ?>">
        <div style="position: absolute; top: 0; left: 100%; transform-origin: top left; transform: rotate(90deg)">
            <div style="width: 90pt; height: 222pt;position: absolute; top: 0; left: 0;">
                <div style="position:absolute; top: 10pt; left: 0; width: 70pt; height: 200pt;">
                    <img style="width: 100%; height: 100%;"
                         src="data:image/jpg;base64,<?php echo base64_encode(file_get_contents(resource_path('views/pdf/ticket_barcode.svg'))); ?>" alt="barcode"/>
                </div>
                <div style="font-family:'Proxima Nova',serif;font-size: 10pt;position: absolute; top: -5pt; left: 71pt; width: 200pt;transform-origin: left bottom; transform: rotate(90deg)">
                    приложите штрих-код к сканеру турникета
                </div>
            </div>
            <div style="width: 223pt; height: 222pt;position: absolute; top: 0; left: 90pt;">
                <div style="width: 223pt; height: 90pt; margin: 0 auto; border-bottom: 1px solid #5e5e5e; font-size: 0">
                    <div style="position: absolute; left: 0; top: 0;">
                        <img src="{{ $ticket->trip->startPier->info->mapLinkQr() }}" alt="qr-link" style="width: 55pt; height: 55pt;margin: 5pt 0 0;">
                    </div>
                    <div style="position: absolute; left: 65pt; top: 2pt; width: 155pt;">
                        <h1 style="font-family:'Reforma Grotesk',serif;font-size: 30pt;margin: 0;text-transform: uppercase;line-height: 28pt;">Посадочный билет</h1>
                    </div>
                    <div style="position: absolute; top: 65pt; left: 0; width: 100%;">
                        <div style="margin: 0;font-family: 'Proxima Nova',serif;font-size: 8pt;line-height: 8pt;">
                            Адрес причала: {{ $ticket->trip->startPier->info->address }}
                        </div>
                        <div style="margin: 0;font-family: 'Proxima Nova',serif;font-size: 8pt;line-height: 8pt;">
                            Телефон причала: {{ $ticket->trip->startPier->info->phone }}
                        </div>
                    </div>
                </div>
                <div style="font-family: 'Proxima Nova',serif; position: absolute; top: 90pt; width: 223pt; height: 132pt; margin: 0 auto;">
                    <img src="{{ $ticket->qr() }}" alt="qr-link" style="position: absolute; top: 45pt; left: 0; width: 80pt; height: 80pt;">

                    <div style="position: absolute; top: 2pt; left: 20pt; width: 203pt; height: 30pt; border-bottom: 1px solid #5e5e5e;">
                        <div style="position: absolute; left: 0; bottom: 0; width: 100%; line-height: 10pt; display: block; font-size: 10pt;">{{ $ticket->trip->excursion->name }}</div>
                    </div>
                    <div style="position:absolute; left: 95pt; top: 30pt; font-size: 7pt">Экскурсия</div>
                    <div style="position:absolute; left: 90pt; top: 43pt; width: 133pt; font-size: 10pt; line-height: 10pt; border-bottom: 1px solid #5e5e5e;">{{ $ticket->trip->start_at->format('d.m.Y,  H:i') }}</div>
                    <div style="position:absolute; left: 95pt; top: 55pt; font-size: 7pt;">Дата поездки</div>
                    <div style="position:absolute; left: 90pt; top: 68pt; width: 133pt; font-size: 10pt; line-height: 10pt; border-bottom: 1px solid #5e5e5e;">{{ $ticket->grade->name }}</div>
                    <div style="position:absolute; left: 95pt; top: 80pt; font-size: 7pt;">Тип билета</div>
                    <div style="position:absolute; left: 90pt; top: 93pt; width: 133pt; font-size: 10pt; line-height: 10pt; border-bottom: 1px solid #5e5e5e;">{{ $ticket->base_price }} рублей</div>
                    <div style="position:absolute; left: 95pt; top: 105pt; font-size: 7pt;">Стоимость</div>
                    <div style="position:absolute; left: 180pt; top: 91pt; width: 133pt; font-size: 10pt;">{{ $ticket->seat_number }}</div>
                    <div style="position:absolute; left: 180pt; top: 105pt; font-size: 7pt;">Место</div>
                    <div style="position:absolute; left: 90pt; top: 115pt; width: 133pt;">
                        <div style="font-size: 8pt;text-align: right">N заказа {{ $ticket->order->additionalData?->provider_order_id ?? $ticket->order_id }}, N билета {{ $ticket->id }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endforeach
</body>
</html>
