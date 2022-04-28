<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExternalProtect
{
    public const COOKIE_NAME = 'ap-external-session';

    /**
     * Check access ability.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return  mixed
     *
     */
    public function handle(Request $request, Closure $next)
    {
        $cookie = $request->cookie(self::COOKIE_NAME);

        try {
            $cookie = json_decode($cookie, true, 512, JSON_THROW_ON_ERROR);

        } catch (Exception $exception) {
            return $this->responseError('Ошибка сессии.', 400, $request->expectsJson());
        }

        if ($request->ip() !== ($cookie['ip'] ?? null)) {
            return $this->responseError('Ошибка сессии. Перезагрузите страницу.', 403, $request->expectsJson());
        }

        return $next($request);
    }

    /**
     * @param string $message
     * @param int $code
     * @param bool $json
     *
     * @return JsonResponse|Response
     */
    protected function responseError(string $message, int $code, bool $json = false)
    {
        if ($json) {
            return response()->json(['message' => $message], $code);
        }
        return response($message, $code);
    }
}
