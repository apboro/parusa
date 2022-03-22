<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleShowcaseCors
{
    /**
     * Handle showcase CORS.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return  mixed
     *
     */
    public function handle(Request $request, Closure $next)
    {
        // For Preflight, return the Preflight response
        if ($this->isPreflightRequest($request)) {

            $response = new Response();

            $response->setStatusCode(204);
            $response->headers->set('Access-Control-Allow-Origin', $request->header('origin', '*'));
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods', 'POST');
            $response->headers->set('Access-Control-Allow-Headers', 'access-control-allow-methods,access-control-allow-origin,content-type,x-requested-with,x-xsrf-token');

            return $response;
        }

        // Handle the request
        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin', $request->header('access-control-allow-origin', '*'));
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Allow-Methods', 'POST');

        return $response;
    }

    public function isPreflightRequest(Request $request): bool
    {
        return $request->getMethod() === 'OPTIONS' && $request->headers->has('Access-Control-Request-Method');
    }
}
