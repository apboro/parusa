<?php

namespace App\LifePos;

use App\Models\Order\Order;
use App\Models\POS\Terminal;
use App\Models\Positions\Position;
use App\Models\Tickets\Ticket;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
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
        // check external IDs
        if (empty($terminal->outlet_id)) {
            throw new RuntimeException('Не задан внешний ID точки продаж для ' . $terminal->name);
        }
        if (empty($terminal->workplace_id)) {
            throw new RuntimeException('Не задан внешний ID для ' . $terminal->name);
        }
        if (empty($position->staffInfo->external_id)) {
            throw new RuntimeException('Не задан внешний ID для сотрудника ' . $position->user->profile->compactName);
        }

        // make connection client
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

        // prepare request
        $total = 0;
        $tickets = [];

        $orderTickets = $order->tickets()->with(['grade', 'trip', 'trip.excursion'])->get();

        foreach ($orderTickets as $ticket) {
            /** @var Ticket $ticket */
            $tickets[] = [
                'good_type' => 'Service',
                'name' => 'Билет ' . $ticket->id . ' (' . mb_strtolower($ticket->grade->name) . ') на ' . $ticket->trip->excursion->name_receipt ?? $ticket->trip->excursion->name
                    . ', рейс ' . $ticket->trip->id . ', ' . $ticket->trip->start_at->format('d.m.Y H:i'),
                'uom' => ['guid' => env('LIFE_POS_TICKET_UOM')],
                'tax' => env('LIFE_POS_TICKET_TAX'),
                'quantity' => 1,
                'sale_price' => ["value" => $ticket->base_price * 100, "currency" => "RUB"],
                'total_sum' => ["value" => $ticket->base_price * 100, "currency" => "RUB"],
            ];
            $total += $ticket->base_price;
        }

        // send order to LifePos and get result
        try {
            if ($order->external_id === null) {
                $data = [
                    'json' => [
                        "outlet" => ["guid" => $terminal->outlet_id],
                        "workplace" => ["guid" => $terminal->workplace_id],
                        "number" => $order->id,
                        "opened_by" => ["guid" => $position->staffInfo->external_id ?? null],
                        "opened_at" => $order->created_at,
                        "status" => "Opened",
//                        'additional_attributes' => ['settlement_location' => $order->terminal?->pier?->info?->address ?? 'Санкт-Петербург, Адмиралтейская набережная д.16'],
                        "total_sum" => ["value" => $total * 100, "currency" => "RUB"],
                        'positions' => $tickets,
                    ],
                ];
                $result = $client->post("/v4/orgs/{$orgId}/deals/sales", $data);
            } else {
                $data = [
                    'json' => [
                        ["op" => "replace", "path" => "outlet", "value" => ["guid" => $terminal->outlet_id]],
                        ["op" => "replace", "path" => "workplace", "value" => ["guid" => $terminal->workplace_id]],
                        ["op" => "replace", "path" => "status", "value" => "Opened"],
                        ["op" => "replace", "path" => "total_sum", "value" => ["value" => $total * 100, "currency" => "RUB"]],
                        ["op" => "replace", "path" => "positions", "value" => $tickets],
                    ],
                ];
                $result = $client->patch("/v4/orgs/{$orgId}/deals/sales/{$order->external_id}", $data);
            }
        } catch (GuzzleException|Exception $exception) {
            Log::channel('lifepos_connection')->error('Connection error: ' . $exception->getMessage());
            Log::channel('lifepos_connection')->info('Request: ' . json_encode($data));
            throw new RuntimeException('LifePos: ' . $exception->getMessage());
        }

        // update order external ID if it was not set and response is OK
        if ($order->external_id === null && $result->getStatusCode() === 201) {
            try {
                $response = json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
                $order->external_id = $response['guid'];
                $order->save();
            } catch (JsonException $exception) {
                Log::channel('lifepos_connection')->error('LifePos response parsing error: ' . $exception->getMessage());
                throw new RuntimeException('LifePos response parsing error: ' . $exception->getMessage());
            }
            return;
        }

        // if order external ID was set no need further actions and response is OK
        if ($order->external_id !== null && $result->getStatusCode() === 204) {
            return;
        }

        // at this point comes only errors
        try {
            $response = json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            Log::channel('lifepos_connection')->error('LifePos response parsing error: ' . $exception->getMessage());
            throw new RuntimeException('LifePos response parsing error: ' . $exception->getMessage());
        }

        Log::channel('lifepos_connection')->error('LifePos error response: ' . $response['message']);
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
            throw new RuntimeException('Этот заказ не связан с продажей.');
        }

        // make connection client
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

        // send data to LifePos
        try {
            $data = [
                'json' => [
                    [
                        "op" => "replace",
                        "path" => "/status",
                        "value" => "Canceled",
                    ],
                    [
                        "op" => "replace",
                        "path" => 'additional_attributes',
                        "value" => ['settlement_location' => $order->terminal?->pier?->info?->address ?? 'Санкт-Петербург, Адмиралтейская набережная д.16'],
                    ]
                ],
            ];
            $result = $client->patch("/v4/orgs/{$orgId}/deals/sales/{$order->external_id}", $data);
        } catch (GuzzleException $exception) {
            Log::channel('lifepos_connection')->error('Connection error: ' . $exception->getMessage());
            Log::channel('lifepos_connection')->info('Request: ' . json_encode($data));
            throw new RuntimeException($exception->getMessage());
        }

        if ($result->getStatusCode() !== 204) {
            $response = json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
            Log::channel('lifepos_connection')->error('LifePos error response: ' . $response['message']);
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

        // make connection client
        $client = new Client([
            'base_uri' => env('LIFE_POS_BASE_URL'),
            'timeout' => 0,
            'allow_redirects' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . env('LIFE_POS_KEY'),
                'Accept-Language' => 'ru-RU',
            ],
        ]);

        // query data
        try {
            $result = $client->get("/v4/orgs/{$orgId}/deals/sales/{$guid}");
        } catch (GuzzleException $exception) {
            Log::channel('lifepos_connection')->error("Querying order [$guid] connection error: " . $exception->getMessage());
            throw new RuntimeException($exception->getMessage());
        }

        try {
            $response = json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            Log::channel('lifepos_connection')->error("Querying order [$guid] response parsing error: " . $exception->getMessage());
            throw new RuntimeException('LifePos response parsing error: ' . $exception->getMessage());
        }

        return $response;
    }

    /**
     * Get sale info.
     *
     * @param string $guid
     *
     * @return  array
     */
    public static function getSalePayments(string $guid): array
    {
        $orgId = env('LIFE_POS_ORG_ID');

        // make connection client
        $client = new Client([
            'base_uri' => env('LIFE_POS_BASE_URL'),
            'timeout' => 0,
            'allow_redirects' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . env('LIFE_POS_KEY'),
                'Accept-Language' => 'ru-RU',
            ],
        ]);

        // query data
        try {
            $result = $client->get("/v4/orgs/{$orgId}/deals/sales/{$guid}/docs/money");
        } catch (GuzzleException $exception) {
            Log::channel('lifepos_connection')->error("Querying order [$guid] connection error: " . $exception->getMessage());
            throw new RuntimeException($exception->getMessage());
        }

        try {
            $response = json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            Log::channel('lifepos_connection')->error("Querying order [$guid] payments response parsing error: " . $exception->getMessage());
            throw new RuntimeException('LifePos response parsing error: ' . $exception->getMessage());
        }

        return $response;
    }


    /**
     * Get sale info.
     *
     * @param string $guid
     *
     * @return  array
     */
    public static function getFiscal(string $guid): array
    {
        $orgId = env('LIFE_POS_ORG_ID');
        $registrarId = env('LIFE_POS_FISCAL_REGISTRAR');

        // make connection client
        $client = new Client([
            'base_uri' => env('LIFE_POS_BASE_URL'),
            'timeout' => 0,
            'allow_redirects' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . env('LIFE_POS_KEY'),
                'Accept-Language' => 'ru-RU',
            ],
        ]);

        // query data
        try {
            $result = $client->get("/v4/orgs/{$orgId}/fiscal-registrars/{$registrarId}/docs/receipts/{$guid}");
        } catch (GuzzleException $exception) {
            Log::channel('lifepos_connection')->error("Querying receipt [$guid] connection error: " . $exception->getMessage());
            throw new RuntimeException($exception->getMessage());
        }

        try {
            $response = json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            Log::channel('lifepos_connection')->error("Querying receipt [$guid] response parsing error: " . $exception->getMessage());
            throw new RuntimeException('LifePos response parsing error: ' . $exception->getMessage());
        }

        return $response;
    }
}
