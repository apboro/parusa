<?php

namespace App\LifePos;

use App\Models\Order\Order;
use App\Models\POS\Terminal;
use App\Models\Positions\Position;
use App\Models\Tickets\Ticket;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use RuntimeException;

class LifePosSales
{
    /**
     * Send order to LifePos.
     *
     * @param Order $order
     * @param Terminal $terminal
     * @param Position $position
     *
     * @return  void
     * @throws RuntimeException
     */
    public static function send(Order $order, Terminal $terminal, Position $position): void
    {
        //if (empty($terminal->organization_id)) {
        //    throw new RuntimeException('Не задан внешний ID организации для ' . $terminal->name);
        //}
        if (empty($terminal->outlet_id)) {
            throw new RuntimeException('Не задан внешний ID точки продаж для ' . $terminal->name);
        }
        if (empty($terminal->workplace_id)) {
            throw new RuntimeException('Не задан внешний ID для ' . $terminal->name);
        }
        if (empty($position->staffInfo->external_id)) {
            throw new RuntimeException('Не задан внешний ID для сотрудника ' . $position->user->profile->compactName);
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
                'quantity' => 1,
                'sale_price' => ["value" => $ticket->base_price * 100, "currency" => "RUB"],
                'total_sum' => ["value" => $ticket->base_price * 100, "currency" => "RUB"],
            ];
            $total += $ticket->base_price;
        }

        try {
            if ($order->external_id === null) {
                $result = $client->post("/v4/orgs/{$orgId}/deals/sales", [
                    'json' => [
                        "outlet" => ["guid" => $terminal->outlet_id],
                        "workplace" => ["guid" => $terminal->workplace_id],
                        "number" => $order->id,
                        "opened_by" => ["guid" => $position->staffInfo->external_id ?? null],
                        "opened_at" => $order->created_at,
                        "status" => "Opened",
                        "total_sum" => ["value" => $total * 100, "currency" => "RUB"],
                        'positions' => $tickets,
                    ],
                ]);
            } else {
                $result = $client->patch("/v4/orgs/{$orgId}/deals/sales/{$order->external_id}", [
                    'json' => [
                        ["op" => "replace", "path" => "outlet", "value" => ["guid" => $terminal->outlet_id]],
                        ["op" => "replace", "path" => "workplace", "value" => ["guid" => $terminal->workplace_id]],
                        ["op" => "replace", "path" => "status", "value" => "Opened"],
                        ["op" => "replace", "path" => "total_sum", "value" => ["value" => $total * 100, "currency" => "RUB"]],
                        ["op" => "replace", "path" => "positions", "value" => $tickets],
                    ],
                ]);
            }
        } catch (GuzzleException|Exception $exception) {
            throw new RuntimeException('LifePos: ' . $exception->getMessage());
        }

        if ($order->external_id === null && $result->getStatusCode() === 201) {
            try {
                $response = json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
                $order->external_id = $response['guid'];
                $order->save();
            } catch (JsonException $exception) {
                throw new RuntimeException('LifePos response parsing error: ' . $exception->getMessage());
            }
            return;
        }

        if ($order->external_id !== null && $result->getStatusCode() === 204) {
            return;
        }

        try {
            $response = json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('LifePos response parsing error: ' . $exception->getMessage());
        }

        throw new RuntimeException('LifePos error response: ' . $response['message']);
    }

    /**
     * Remove order from LifePos.
     *
     * @param Order $order
     *
     * @return  void
     * @throws JsonException
     * @throws RuntimeException
     */
    public static function cancel(Order $order): void
    {
        if ($order->external_id === null) {
            throw new RuntimeException('Зтот заказ не связан с продажей.');
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

        try {
            $result = $client->patch("/v4/orgs/{$orgId}/deals/sales/{$order->external_id}", [
                'json' => [[
                    "op" => "replace",
                    "path" => "/status",
                    "value" => "Canceled",
                ]],
            ]);
        } catch (GuzzleException $exception) {
            throw new RuntimeException($exception->getMessage());
        }

        if ($result->getStatusCode() !== 204) {
            $response = json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
            throw new RuntimeException($response['message']);
        }
    }

    /**
     * Get sale info.
     *
     * @param string $guid
     *
     * @return  array
     */
    public static function getSale(string $guid): array
    {
        $orgId = env('LIFE_POS_ORG_ID');

        $client = new Client([
            'base_uri' => env('LIFE_POS_BASE_URL'),
            'timeout' => 0,
            'allow_redirects' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . env('LIFE_POS_KEY'),
                'Accept-Language' => 'ru-RU',
            ],
        ]);

        try {
            $result = $client->get("/v4/orgs/{$orgId}/deals/sales/{$guid}");
        } catch (GuzzleException $exception) {
            throw new RuntimeException($exception->getMessage());
        }

        try {
            $response = json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('LifePos response parsing error: ' . $exception->getMessage());
        }

        return $response;
    }
}
