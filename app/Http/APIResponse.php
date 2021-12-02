<?php

namespace App\Http;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class APIResponse
{
    /**
     * Make 304 response.
     *
     * @return  JsonResponse
     */
    public static function notModified(): JsonResponse
    {
        return response()->json(null, 304);
    }

    /**
     * Make 403 response.
     *
     * @return  JsonResponse
     */
    public static function forbidden(): JsonResponse
    {
        return response()->json([
            'status' => 'Forbidden',
            'code' => 403,
        ], 403);
    }

    /**
     * Make 404 response.
     *
     * @return  JsonResponse
     */
    public static function notFound(): JsonResponse
    {
        return response()->json([
            'status' => 'Not found',
            'code' => 404,
        ], 404);
    }

    /**
     * Make 404 response.
     *
     * @param string $status
     *
     * @return  JsonResponse
     */
    public static function error(string $status = 'Server error'): JsonResponse
    {
        return response()->json([
            'status' => app()->isProduction() ? 'Server error' : $status,
            'code' => 500,
        ], 500);
    }

    /**
     * Make 200 response with data.
     *
     * @param mixed $data
     *
     * @return  JsonResponse
     */
    public static function response($data): JsonResponse
    {
        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'data' => $data,
        ], 200);
    }

    /**
     * Make 200 response with data.
     *
     * @param mixed $list
     * @param array|null $titles
     * @param array|null $payload
     * @param Carbon|null $lastModified
     *
     * @return  JsonResponse
     */
    public static function list($list, ?array $titles = null, ?array $payload = null, ?Carbon $lastModified = null): JsonResponse
    {
        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'list' => $list,
            'titles' => $titles,
            'payload' => $payload,
        ], 200, self::lastModHeaders($lastModified));
    }

    /**
     * Make 200 response with data.
     *
     * @param mixed $list
     * @param array|null $titles
     * @param array|null $payload
     * @param Carbon|null $lastModified
     *
     * @return  JsonResponse
     */
    public static function paginationList($list, ?array $titles = null, ?array $payload = null, ?Carbon $lastModified = null): JsonResponse
    {
        if (!is_array($list) && method_exists($list, 'toArray')) {
            $list = $list->toArray();
        }

        return response()->json([
            'status' => 'OK',
            'code' => 200,
            'list' => $list['data'],
            'titles' => $titles,
            'payload' => $payload,
            'pagination' => [
                'current_page' => $list['current_page'],
                'last_page' => $list['last_page'],
                'from' => $list['from'] ?? 0,
                'to' => $list['to'] ?? 0,
                'total' => $list['total'],
                'per_page' => $list['per_page'],
            ],
        ], 200, self::lastModHeaders($lastModified));
    }

    /**
     * Add last modifier header to response.
     * Modified timestamp must be GMT timezone.
     *
     * @param Carbon|null $lastMod
     * @param array $headers
     *
     * @return  array
     */
    private static function lastModHeaders(?Carbon $lastMod, array $headers = []): array
    {
        if ($lastMod === null) {
            return $headers;
        }

        return array_merge($headers, [
            'Last-Modified' => $lastMod->clone()->format('D, d M Y H:i:s') . ' GMT',
        ]);
    }
}
