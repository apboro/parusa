<?php

namespace App\Services\CityTourBus;

use Illuminate\Support\Facades\Http;

class CityTourApiClientProvider
{
    private ?string $baseUrl;
    private array $headers;

    public function __construct()
    {
        $this->baseUrl = config('city-tour-bus.api_url');
        $this->headers = [
            'Authorization' => 'Bearer ' .config('city-tour-bus.api_key'),
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
    public function put(string $uri, array $data = []): array
    {
        $response = Http::withHeaders($this->headers)
            ->put($this->baseUrl . $uri, $data);

        return [
            'status' => $response->status(),
            'headers' => $response->headers(),
            'body' => $response->json(),
        ];
    }
    public function delete(string $uri, array $data = []): array
    {
        $response = Http::withHeaders($this->headers)
            ->delete($this->baseUrl . $uri, $data);

        return [
            'status' => $response->status(),
            'headers' => $response->headers(),
            'body' => $response->json(),
        ];
    }
}
