<?php

namespace App\LifePos;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class LifePosOrg
{
    public static function setCallBackUrl(): void
    {
        $client = new Client([
            'base_uri' => env('LIFE_POS_BASE_URL'),
            'timeout' => 0,
            'allow_redirects' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . env('LIFE_POS_KEY'),
                'Accept-Language' => 'ru-RU',
                'X-LP-Client-Extensions' => 'notification_service'
            ],
        ]);

        $orgId = env('LIFE_POS_ORG_ID');

        try {
            $result = $client->patch("/v4/orgs/{$orgId}", [
                'json' => [[
                    "op" => "add",
                    "path" => "/extensions/notification_service",
                    "value" => [
                        "turned_on" => true,
                        "primary_url_for_notifications" => route('lifePosNotification'),
                        "version" => "1",
                    ],
                ]],
            ]);
        } catch (GuzzleException $exception) {
            try {
                throw new RuntimeException($exception->getMessage());
            } catch (Exception $exception) {
                throw new RuntimeException($exception->getMessage());
            }
        }

        if ($result->getStatusCode() !== 204) {
            $response = json_decode($result->getBody(), true, 512, JSON_THROW_ON_ERROR);
            throw new RuntimeException($response['message']);
        }
    }
}
