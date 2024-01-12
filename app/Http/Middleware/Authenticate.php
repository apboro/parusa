<?php

namespace App\Http\Middleware;

use App\Http\Response;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Authenticate extends Middleware
{
    public function handle($request, $next, ...$guards)
    {
        if (!Auth::guard('sanctum')->check() && Str::contains($request->route()->getName(), 'api.v1') ) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $this->authenticate($request, $guards);

        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param Request $request
     *
     * @return  string|null
     */
    protected function redirectTo($request): ?string
    {
        if (!$request->expectsJson()) {
            return route('login');
        }

        return Response::forbiddenResponse();
    }
}
