<?php

namespace App\NevaTravel;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class ApiClientProvider
{
    private Client $httpClient;
    private string $baseUrl;
    private array $headers;

    public function __construct()
    {
        $this->httpClient = new Client();
        $this->baseUrl = env('NEVA_TRAVEL_API');
        $this->headers =             [
            'X-API-KEY'=>env('NEVA_TRAVEL_API_KEY'),
            'Accept'=>'application/json'
        ];
    }

    public function get(string $uri, array $query = [])
    {
        $options = [
            'headers' => $this->headers,
            'query' => $query,
        ];

        return $this->request('GET', $uri, $options);
    }

    public function post(string $uri, array $data = [])
    {
        $options = [
            'headers' => $this->headers,
            'json' => $data,
        ];

        return $this->request('POST', $uri, $options);
    }

    private function request(string $method, string $uri, array $options = [])
    {
        try {
            $response = $this->httpClient->request($method, $this->baseUrl . $uri, $options);

            $body = (string)$response->getBody();

            return [
                'status' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body' => json_decode($body, true),
            ];
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $body = (string)$response->getBody();

            return [
                'status' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body' => json_decode($body, true),
            ];
        }
    }
}
