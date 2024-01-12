<?php

namespace App\Http\Middleware;

use App\Http\APIResponse;
use App\Models\Dictionaries\PartnerStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPartnerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $partner = auth()->user();

        if (!$partner || $partner->status_id === PartnerStatus::blocked) {
            return APIResponse::forbidden();
        }

        return $next($request);
    }
}
