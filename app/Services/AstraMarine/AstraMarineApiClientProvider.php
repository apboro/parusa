<?php

namespace App\Services\AstraMarine;

use Illuminate\Support\Facades\Http;

class AstraMarineApiClientProvider
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('astra-marine.api_url');
    }

    public function get(string $uri, array $query = []): array
    {
        $response = Http::withBasicAuth(config('astra-marine.username'), config('astra-marine.password'))
        ->timeout(10)->get($this->baseUrl . $uri, $query);

        return [
            'status' => $response->status(),
            'headers' => $response->headers(),
            'body' => $response->json(),
        ];
    }

    public function post(string $uri, array $data = []): array
    {
        $data['email'] = 'info@parus-a.ru';
        $response = Http::withBasicAuth(config('astra-marine.username'), config('astra-marine.password'))
            ->timeout(10)->post($this->baseUrl . $uri, $data);

        return [
            'status' => $response->status(),
            'headers' => $response->headers(),
            'body' => $response->json(),
        ];
    }
}
