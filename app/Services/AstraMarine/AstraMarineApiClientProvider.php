<?php

namespace App\Services\AstraMarine;

use App\Exceptions\AstraMarine\AstraMarineApiConnectException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AstraMarineApiClientProvider
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('astra-marine.api_url');
    }

    /**
     * @throws AstraMarineApiConnectException
     */
    public function get(string $uri, array $query = []): array
    {
        try {
            $response = Http::withBasicAuth(config('astra-marine.username'), config('astra-marine.password'))
                ->timeout(3)->get($this->baseUrl . $uri, $query);
        } catch (ConnectionException $e) {
            Log::channel('astra-marine')->error($e);
            throw new AstraMarineApiConnectException();
        }

        return [
            'status' => $response->status(),
            'headers' => $response->headers(),
            'body' => $response->json(),
        ];
    }

    /**
     * @throws AstraMarineApiConnectException
     */
    public function post(string $uri, array $data = []): array
    {
        $data['email'] = 'info@parus-a.ru';

        try {
            $timeout = $this->getTimeoutForBaseUrl($uri);
            $response = Http::withBasicAuth(config('astra-marine.username'), config('astra-marine.password'))
                ->timeout($timeout)->post($this->baseUrl . $uri, $data);
        } catch (ConnectionException $e) {
            Log::channel('astra-marine')->error($e);
            throw new AstraMarineApiConnectException();
        }

        return [
            'status' => $response->status(),
            'headers' => $response->headers(),
            'body' => $response->json(),
        ];
    }

    private function getTimeoutForBaseUrl($baseUrl): int
    {
        $needTimeoutMethods = ['getSeatsOnEvent', 'registerOrder', 'confirmPayment', 'returnOrder', 'bookingSeat'];
        if (in_array($baseUrl, $needTimeoutMethods) !== false) {
            return 5;
        } else {
            return 500;
        }
    }
}
