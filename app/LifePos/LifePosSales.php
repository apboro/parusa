<?php

namespace App\LifePos;

use App\Models\Dictionaries\OrderStatus;
use App\Models\POS\Terminal;
use App\Models\Positions\Position;
use App\Models\Tickets\Order;
use App\Models\Tickets\Ticket;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use RuntimeException;

class LifePosSales
{
    /**
     * @param Order $order
     * @param Terminal $terminal
     * @param Position $position
     *
     * @return  void
     * @throws JsonException
     * @throws RuntimeException
     */
    public static function send(Order $order, Terminal $terminal, Position $position): void
    {
        if ($order->external_id !== null) {
             throw new RuntimeException('Зтот заказ уже отправллен в оплату');
         }

        $client = new Client([
            'base_uri' => env('LIFE_POS_BASE_URL'),
            'timeout' => 0,
            'allow_redirects' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . env('LIFE_POS_KEY'),
                'Accept-Language' => 'ru-RU',
            ],
        ]);

        $orgId = env('LIFE_POS_ORG_ID');

        $total = 0;
        $tickets = [];

        $orderTickets = $order->tickets()->with(['grade', 'trip', 'trip.excursion'])->get();

        foreach ($orderTickets as $ticket) {
            /** @var Ticket $ticket */
            $tickets[] = [
                'good_type' => 'Service',
                'name' => 'Билет №' . $ticket->id . ' (' . mb_strtolower($ticket->grade->name) . ')  на экскурсию ' . $ticket->trip->excursion->name
                    . ', рейс №' . $ticket->trip->id . ', ' . $ticket->trip->start_at->format('d.m.Y H:i'),
                'uom' => ['guid' => env('LIFE_POS_TICKET_UOM')],
                'tax' => env('LIFE_POS_TICKET_TAX'),
                'sale_price' => [
                    "value" => $ticket->base_price * 100,
                    "currency" => "RUB",
                ],
                'quantity' => 1,
                'total_sum' => [
                    "value" => $ticket->base_price * 100,
                    "currency" => "RUB",
                ],
            ];
            $total += $ticket->base_price;
        }

        try {
            $result = $client->post("/v4/orgs/{$orgId}/deals/sales", [
                'json' => [
                    "outlet" => ["guid" => $terminal->outlet_id],
                    "workplace" => ["guid" => $terminal->workplace_id],
                    "number" => $order->id,
                    "opened_by" => ["guid" => $position->staffInfo->external_id ?? null],
                    "opened_at" => $order->created_at,
                    "status" => "Opened",
                    "total_sum" => [
                        "value" => $total * 100,
                        "currency" => "RUB",
                    ],
                    'positions' => $tickets,
                ],
            ]);
        } catch (GuzzleException $exception) {
            throw new RuntimeException($exception->getMessage());
        }

        if ($result->getStatusCode() !== 201) {
            $response = json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
            throw new RuntimeException($response['message']);
        }

        $response = json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $order->external_id = $response['guid'];
        $order->save();
    }
}
