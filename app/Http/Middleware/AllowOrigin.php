<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AllowOrigin
{
    public const COOKIE_NAME = 'ap-showcase-session';

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
        return $next($request)
            ->header('Access-Control-Allow-Origin', $request->header('access-control-allow-origin', '*'))
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'POST');
    }
}
