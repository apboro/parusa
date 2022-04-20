<?php

use App\Models\Tickets\Ticket;

/** @var Ticket $ticket */
?>

    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>Билет N{{ $ticket->id }}</title>
    <style>
        @page {
            size: 340.15pt 226.77pt;
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body style="margin: 0; padding: 0;">
<div style="width: 340.15pt;margin: 7pt 0 0;">
    <table style="width: 315pt; height: 90pt; margin: 0 auto; border-bottom: 1px solid #5e5e5e;">
        <tr>
            <td style="vertical-align: top; width: 170pt;">
                <h1 style="font-family:'Reforma Grotesk',serif;font-size: 34pt;margin: 0;text-transform: uppercase;line-height: 36pt;">Посадочный билет</h1>
            </td>
            <td style="text-align: right; vertical-align: top;">
                <div style="margin: 0;font-family: 'Proxima Nova',serif;font-size: 7pt;line-height: 7pt;">
                    Адрес причала:
                </div>
               <div style="margin: 0;font-family: 'Proxima Nova',serif;font-size: 7pt;line-height: 7pt;">
                    <i>{{ $ticket->trip->startPier->info->address }}</i>
                </div>
                <div style="text-align: right">
                    <img src="{{ $ticket->trip->startPier->info->mapLinkQr() }}" alt="qr-link" style="width: 55pt; height: 55pt;margin: 5pt 0 0;">
                </div>
            </td>
        </tr>
    </table>

    <table style="width: 315pt; margin: 0 auto;">
        <tr>
            <td style="width: 90pt; vertical-align: top;padding-top: 10pt;">
                <img src="{{ $ticket->qr() }}" alt="qr-link" style="width: 75pt; height: 75pt;">
                <div style="margin: 5pt 0 3pt;font-family: 'Proxima Nova',serif;font-size: 8pt;line-height: 7pt;">№ заказа {{ $ticket->order_id }}</div>
                <div style="margin: 0;font-family: 'Proxima Nova',serif;font-size: 8pt;line-height: 7pt;">№ билета {{ $ticket->id }}</div>
            </td>
            <td style="vertical-align: top; padding-top: 5pt;">
                <table style="width: 100%; font-family: 'Proxima Nova',serif;font-size: 7pt;">
                    <tr>
                        <td style="width: 65pt;vertical-align: top; padding-right: 3pt;">Экскурсия</td>
                        <td style="vertical-align: top; border-bottom: 1px solid #5e5e5e;">{{ $ticket->trip->excursion->name }}</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; padding-right: 3pt;">Телефон причала</td>
                        <td style="vertical-align: top; border-bottom: 1px solid #5e5e5e;">{{ $ticket->trip->startPier->info->phone }}</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; padding-right: 3pt;">Дата поездки</td>
                        <td style="vertical-align: top; border-bottom: 1px solid #5e5e5e;">{{ $ticket->trip->start_at->format('d.m.Y,  H:i') }}</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; padding-right: 3pt;">Тип билета</td>
                        <td style="vertical-align: top; border-bottom: 1px solid #5e5e5e;">{{ $ticket->grade->name }}</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; padding-right: 3pt;">Стоимость</td>
                        <td style="vertical-align: top; border-bottom: 1px solid #5e5e5e;">{{ $ticket->base_price }} рублей</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
