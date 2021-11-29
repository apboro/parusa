<?php

namespace App\Http;

use Illuminate\Http\JsonResponse;

class Response
{
    /**
     * Make 200 response with data.
     *
     * @param mixed $data
     *
     * @return  JsonResponse
     */
    public static function response($data): JsonResponse
    {
        return response()->json(['status' => 'OK', 'data' => $data], 200);
    }

    /**
     * Make 403 response.
     *
     * @return  JsonResponse
     */
    public static function forbiddenResponse(): JsonResponse
    {
        return response()->json(['status' => 'Forbidden'], 403);
    }

    /**
     * Make 404 response.
     *
     * @return  JsonResponse
     */
    public static function notFoundResponse(): JsonResponse
    {
        return response()->json(['status' => 'Not found'], 404);
    }

    /**
     * Make 404 response.
     *
     * @param string $status
     *
     * @return  JsonResponse
     */
    public static function errorResponse(string $status = 'Server error'): JsonResponse
    {
        return response()->json(['status' => app()->isProduction() ? 'Server error' : $status], 500);
    }
}
