Заказ {{ $order->id }}<br>
Телефон клиента: {{ $order->phone }}<br>
Билетов {{ $order->tickets->count() }}<br>
@foreach ($order->tickets as $ticket)
    <ul>
        <li>Билет: {{ $ticket->id }}</li>
        <li>Экскурсия: {{ $ticket->trip->excursion->name }}</li>
        <li>Начало в: {{ $ticket->trip->start_at }}</li>
    </ul>
@endforeach
