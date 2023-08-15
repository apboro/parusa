<?php

namespace App\Services\NevaTravel;

use Illuminate\Support\Facades\Http;

class ApiClientProvider
{
    private ?string $baseUrl;
    private array $headers;

    public function __construct()
    {
        $this->baseUrl = config('neva-travel.api_url');
        $this->headers = [
            'X-API-KEY' => config('neva-travel.api_key'),
            'Accept' => 'application/json'
        ];
    }

    public function get(string $uri, array $query = []): array
    {
        $response = Http::withHeaders($this->headers)
            ->get($this->baseUrl . $uri, $query);

        return [
            'status' => $response->status(),
            'headers' => $response->headers(),
            'body' => $response->json(),
        ];
    }

    public function post(string $uri, array $data = []): array
    {
        $response = Http::withHeaders($this->headers)
            ->post($this->baseUrl . $uri, $data);

        return [
            'status' => $response->status(),
            'headers' => $response->headers(),
            'body' => $response->json(),
        ];
    }
}
