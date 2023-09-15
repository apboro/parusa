<?php

namespace App\LifePos;

use App\Jobs\FakeLifePosNotification;
use App\LifePos\LifePosSales;
use App\Models\Order\Order;
use App\Models\POS\Terminal;
use App\Models\Positions\Position;
use App\Models\Tickets\Ticket;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MockLifePos
{
    public static $mockResponseData = [];

    public static function send(Order $order): void
    {
        $order->external_id = 'test-'.Str::random('12');
        $order->save();

        FakeLifePosNotification::dispatch($order);
    }

    public static function cancel(Order $order): void
    {
        self::$mockResponseData['cancel'] = ['status' => 'success', 'message' => 'Order canceled successfully'];
    }

    public static function getSale(string $guid): array
    {
        self::$mockResponseData['getSale'] = ['status' => 'success', 'message' => 'Sale info retrieved successfully'];
        return self::$mockResponseData['getSale'];
    }

    public static function getSalePayments(string $guid): array
    {
        self::$mockResponseData['getSalePayments'] = ['status' => 'success', 'message' => 'Sale payments retrieved successfully'];
        return self::$mockResponseData['getSalePayments'];
    }

    public static function getFiscal(string $guid): array
    {
        self::$mockResponseData['getFiscal'] = ['status' => 'success', 'message' => 'Fiscal info retrieved successfully'];
        return self::$mockResponseData['getFiscal'];
    }
}

