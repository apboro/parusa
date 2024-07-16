Заказ {{ $order->id }}<br>
Билетов {{ $order->tickets->count() }}<br>
@foreach ($order->tickets as $ticket)
    Билет: {{ $ticket->id }}<br>
    Экскурсия: {{ $ticket->trip->excursion->name }}<br>
    Начало в: {{ $ticket->trip->start_at }}<br>
@endforeach
