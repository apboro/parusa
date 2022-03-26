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
        html, body {
            font-family: DejaVu Sans;
            margin: 0;
        }

        h1 {
            font-size: 12px;
            margin: 3px 0;
        }

        p {
            font-size: 10px;
            margin: 3px 0;
        }
    </style>
</head>
<body>
<div style="margin: 5px; padding: 5px; border: 1px solid #1e1e1e;">
    <h1>Билет N{{ $ticket->id }}</h1>
    <p>Дата: <b>{{ $ticket->trip->start_at->translatedFormat('j F Y') }}</b></p>
    <p>Время: <b>{{ $ticket->trip->start_at->format('H:i') }}</b></p>
    <p>Экскурсия: <b>{{ $ticket->trip->excursion->name }}</b></p>
    <p>Причал: <b>{{ $ticket->trip->startPier->name }}</b></p>
    <p>Тип билета: <b>{{ $ticket->grade->name }}</b></p>
    <p>Цена: <b>{{ $ticket->base_price }} руб.</b></p>
</div>
</body>
</html>
