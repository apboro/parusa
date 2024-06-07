<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogApiv1Middleware
{
    public function handle(Request $request, Closure $next)
    {
        $data = json_encode($request->all());
        $partner = auth()->user()?->positions[0]->partner?->name;
        Log::channel('apiv1')->info($partner . ' from: '. $request->url() . ' with data: '. $data);
        return $next($request);
    }
}
