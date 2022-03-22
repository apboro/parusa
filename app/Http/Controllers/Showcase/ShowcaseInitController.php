<?php

namespace App\Http\Controllers\Showcase;

use App\Http\Controllers\ApiController;
use App\Http\Middleware\ExternalProtect;
use App\Models\Dictionaries\ExcursionProgram;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JsonException;

class ShowcaseInitController extends ApiController
{
    /**
     * Initial data for showcase application.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws JsonException
     */
    public function init(Request $request): JsonResponse
    {
        $originalCookie = $request->cookie(ExternalProtect::COOKIE_NAME);
        try {
            $originalCookie = $originalCookie ? json_decode($originalCookie, true, 512, JSON_THROW_ON_ERROR) : null;
        } catch (Exception $exception) {
            return response()->json(['message' => 'Ошибка сессии.'], 400);
        }

        $cookie = [
            'ip' => $request->ip(),
            'user-agent' => $request->userAgent(),
            'partner' => $originalCookie['partner'] ?? $request->input('partner'),
            'media' => $originalCookie['media'] ?? $request->input('media'),
        ];

        $programs = ExcursionProgram::query()->where(['enabled' => true])->select(['id', 'name'])->orderBy('name')->get();

        return response()->json([
            'today' => Carbon::now()->format('Y-m-d'),
            'programs' => $programs->toArray(),
            'date_from' => Carbon::now()->format('Y-m-d'),
            'date_to' => null,
        ])->withCookie(cookie(
            ExternalProtect::COOKIE_NAME,
            json_encode($cookie, JSON_THROW_ON_ERROR),
            0,
            null,
            null,
            true,
            true,
            false,
            'None'
        ));
    }
}
