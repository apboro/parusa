<?php

namespace App\Services\YagaAPI;

use App\Actions\CreateOrderFromYaga;
use App\Actions\CreateTicketsFromYaga;
use App\Events\NevaTravelCancelOrderEvent;
use App\Events\NevaTravelOrderPaidEvent;
use App\Events\NewNevaTravelOrderEvent;
use App\Helpers\PriceConverter;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Order\Order;
use App\Models\Sails\Trip;
use App\Models\Tickets\Ticket;
use App\Services\YagaAPI\Model\AvailableSeats;
use App\Services\YagaAPI\Model\OrderInfo;
use App\Services\YagaAPI\Requests\Order\ApproveOrderRequest;
use App\Services\YagaAPI\Requests\Order\AvailableSeatsRequest;
use App\Services\YagaAPI\Requests\Order\CancelOrderRequest;
use App\Services\YagaAPI\Requests\Order\ClearReservationRequest;
use App\Services\YagaAPI\Requests\Order\OrderInfoRequest;
use App\Services\YagaAPI\Requests\Order\OrderStatusRequest;
use App\Services\YagaAPI\Requests\Order\ReserveTicketsRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class YagaOrderApiController
{
    public function availableSeats(AvailableSeatsRequest $request): JsonResponse
    {
        $data = $request->all();

        if (empty($data['sessionId'])) {
            return response()->json('В запросе нет sessionId', 400);
        }

        $trip = Trip::query()->activeScarletSails()
            ->where('id', $data['sessionId'])
            ->first();

        if (!$trip) {
            return response()->json('Рейс не найден', 400);
        }

        if (!empty($data['venueId']) && $trip->start_pier_id != $data['venueId']) {
            return response()->json();
        }
        if (!empty($data['eventId']) && $trip->excursion->id != $data['eventId']) {
            return response()->json();
        }
        if (!empty($data['hallId']) && $trip->start_pier_id != $data['hallId']) {
            return response()->json();
        }
        if (!empty($data['sessionTime']) && $trip->start_at->timestamp != $data['sessionTime']) {
            return response()->json();
        }

        $results = AvailableSeats::getResource($trip);

        return response()->json($results);
    }


    public function reserve(ReserveTicketsRequest $request): JsonResponse
    {
        $data = $request->all();

        if (!$data['sessionId']) {
            return response()->json('В запросе отсутствует sessionId');
        }

        $trip = Trip::with('excursion')
            ->where('id', $data['sessionId'])
            ->first();

        if (!$trip) {
            return response()->json('Рейс не найден');
        }

        if (!empty($data['venueId']) && $trip->start_pier_id != $data['venueId']) {
            return response()->json('wrong venueID');
        }

        if (!empty($data['eventId']) && $trip->excursion->id != $data['eventId']) {
            return response()->json('wrong eventId');
        }
        if (!empty($data['hallId']) && $trip->start_pier_id != $data['hallId']) {
            return response()->json('wrong hallId');
        }

        $rateList = $trip->getRate();
        if (($trip->start_at < now() && $trip->excursion->is_single_ticket == 0)
            || ($trip->excursion->is_single_ticket != 0 && $trip->start_at < now() && !$trip->start_at->isCurrentDay())
            || !$rateList
            || !$trip->hasStatus(TripStatus::regular)
            || !$trip->hasStatus(TripSaleStatus::selling, 'sale_status_id')
        ) {
            return response()->json('Рейс не актуален');
        }

        if (empty($data['items'])) {
            return response()->json('В запросе нет билетов');
        }

        if (!$this->areEnoughTicketsAvailable($trip, $data)) {
            return response()->json('Недостаточно билетов');
        }

        foreach ($data['items'] as $ticket) {
            $rate = $rateList->rates()->where('grade_id', $ticket['categoryId'])->first();
            if (!$rate) {
                return response()->json('Неправильно указана категория билета - ' . $ticket['categoryId']);
            }

            if (!$rate->partner_price) {
                return response()->json('Для данной категории билета не установлена цена');
            }
        }

        DB::transaction(function () use ($trip, $data, &$order, $rate) {
            $newTickets = (new CreateTicketsFromYaga($data, $trip, $rate))->execute();

            if (empty($newTickets)) {
                return response()->json('Не удалось оформить билеты');
            }

            $order = (new CreateOrderFromYaga($data, $newTickets))->execute();
        });

        NewNevaTravelOrderEvent::dispatch(Order::find($order['id']));

        return response()->json($order);
    }

    public function orderInfo(OrderInfoRequest $request): JsonResponse
    {
        $order = Order::with(['status', 'tickets', 'partner', 'tickets.grade'])
            ->whereIn('status_id', OrderStatus::yaga_statuses)
            ->where('id', $request->id)->first();

        if (!$order) {
            return response()->json('Заказ не найден');
        }

        return response()->json((new OrderInfo($order))->getResource());
    }

    public function orderStatus(OrderStatusRequest $request): JsonResponse
    {
        $order = Order::with('status')->find($request->id);
        $status = match ($order->status->id) {
            OrderStatus::yaga_confirmed => 'APPROVED',
            OrderStatus::yaga_reserved => 'RESERVED',
            OrderStatus::yaga_canceled => 'CANCELLED',
            default => 'UNDEFINED_ORDER_STATUS'
        };

        return response()->json([
            "id" => $order->id,
            "orderNumber" => $order->id,
            "specificFields" => (object)[],
            "status" => $status
        ]);
    }

    public function cancelOrder(CancelOrderRequest $request): JsonResponse
    {
        $order = Order::with(['status', 'tickets', 'tickets.trip'])->find($request->id);

        if (!$request->cancelItems) {
            if ($order->hasStatus(OrderStatus::yaga_confirmed)) {
                foreach ($order->tickets as $ticket) {
                    $ticket->setStatus(TicketStatus::yaga_canceled);
                }
                $order->setStatus(OrderStatus::yaga_canceled);
            }
        } else {
            foreach ($request->cancelItems as $item) {
                $ticket = $order->tickets->find($item['ticketId']);
                if (PriceConverter::priceToStore($ticket->base_price) > $item['refundedCost']['total']['value']) {
                    $ticket->setStatus(TicketStatus::yaga_canceled_with_penalty);
                    $ticket->additionalData()->updateOrCreate([
                        'provider_id' => Provider::scarlet_sails,
                        'penalty_sum' => PriceConverter::priceToStore($ticket->base_price) - $item['refundedCost']['total']['value']
                    ]);
                    $order->setStatus(OrderStatus::yaga_canceled_with_penalty);
                } else {
                    $ticket->setStatus(TicketStatus::yaga_canceled);
                    $order->setStatus(OrderStatus::yaga_canceled);
                }
            }
        }

        $statusId = $order->status_id;
        $status = match ($statusId) {
            OrderStatus::yaga_confirmed => 'APPROVED',
            OrderStatus::yaga_reserved => 'RESERVED',
            OrderStatus::yaga_canceled, OrderStatus::yaga_canceled_with_penalty => 'CANCELLED',
            default => 'UNDEFINED_ORDER_STATUS'
        };

        NevaTravelCancelOrderEvent::dispatch($order);

        return response()->json([
            "id" => $order->id,
            "orderNumber" => $order->id,
            "specificFields" => (object)[],
            "status" => $status
        ]);
    }


    public function clearReservation(ClearReservationRequest $request)
    {
        $order = Order::with(['status', 'tickets', 'tickets.trip'])->find($request->id);

        if ($order->hasStatus(OrderStatus::yaga_reserved)) {
            foreach ($order->tickets as $ticket) {
                $ticket->setStatus(TicketStatus::yaga_canceled);
            }
            $order->setStatus(OrderStatus::yaga_canceled);
        } else {
            return response()->json(['message' => 'Неверный статус для отмены']);
        }
        $statusId = $order->status_id;
        $status = match ($statusId) {
            OrderStatus::yaga_confirmed => 'APPROVED',
            OrderStatus::yaga_reserved => 'RESERVED',
            OrderStatus::yaga_canceled => 'CANCELLED',
            default => 'UNDEFINED_ORDER_STATUS'
        };

        NevaTravelCancelOrderEvent::dispatch($order);

        return response()->json([
            "id" => $order->id,
            "orderNumber" => $order->id,
            "specificFields" => (object)[],
            "status" => $status
        ]);
    }

    public function approve(ApproveOrderRequest $request): JsonResponse
    {
        $order = Order::with(['status', 'tickets', 'partner'])
            ->whereIn('status_id', OrderStatus::yaga_statuses)
            ->where('id', $request->id)->first();

        if (!$order) {
            return response()->json('Заказ не найден');
        }

        if (!$order->hasStatus(OrderStatus::yaga_reserved)) {
            return response()->json('Заказ не находится в резерве');
        }

        try {
            DB::transaction(static function () use (&$order) {
                $order->setStatus(OrderStatus::yaga_confirmed);
                $order->tickets->each(fn(Ticket $ticket) => $ticket->setStatus(TicketStatus::yaga_confirmed));
                $order->payCommissions();
            });
        } catch (Exception $e) {
            Log::channel('yaga')->error($e);
        }

        NevaTravelOrderPaidEvent::dispatch($order);

        return response()->json(['id' => $order->id, 'orderNumber' => $order->id, 'status' => 'APPROVED', 'specificFields' => (object)[]]);
    }

    private function areEnoughTicketsAvailable($trip, $data): bool
    {
        $ticketsLeft = $trip->tickets()
            ->whereIn('status_id', TicketStatus::ticket_countable_statuses)
            ->count();
        $ticketsNeed = count($data['items']);

        return ($ticketsLeft + $ticketsNeed) <= $trip->tickets_total;
    }

}
