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
        }

        h1 {
            font-size: 16px;
        }

        p {
            font-size: 14px;
        }
    </style>
</head>
<body>
<h1>Билет N{{ $ticket->id }}</h1>
<p>Дата: <b>{{ $ticket->trip->start_at->translatedFormat('j F Y') }}</b></p>
<p>Время: <b>{{ $ticket->trip->start_at->format('H:i') }}</b></p>
<p>Экскурсия: <b>{{ $ticket->trip->excursion->name }}</b></p>
<p>Причал: <b>{{ $ticket->trip->startPier->name }}</b></p>
<p>Тип билета: <b>{{ $ticket->grade->name }}</b></p>
<p>Цена: <b>{{ $ticket->base_price }} руб.</b></p>
</body>
</html>
