<?php

namespace App\Http\Middleware;

use App\Http\APIResponse;
use App\Http\Response;
use App\Models\Dictionaries\Role;
use App\Models\User\Helpers\Currents;
use Closure;
use Illuminate\Http\Request;

class Allow
{
    /**
     * Check access ability.
     *
     * @param Request $request
     * @param Closure $next
     * @param mixed ...$rules
     *
     * @return  mixed
     *
     */
    public function handle(Request $request, Closure $next, ...$rules)
    {
        if (empty($rules)) {
            return $next($request);
        }

        $current = Currents::get($request);
        $isStaff = $current->isStaff();

        foreach ($rules as $rule) {
            $set = explode('_', $rule);
            if ($set[0] === 'partner' && !$isStaff) {
                return $next($request);
            }
            if ($set[0] === 'staff' && $isStaff && !isset($set[1])) {
                return $next($request);
            }
            if ($set[0] === 'staff' && $isStaff && isset($set[1]) && $set[1] === 'admin' && $current->role() && $current->role()->matches(Role::admin)) {
                return $next($request);
            }
            if ($set[0] === 'staff' && $isStaff && isset($set[1]) && $set[1] === 'terminal' && $current->role() && $current->role()->matches(Role::terminal)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return APIResponse::forbidden();
        }

        return Response::forbiddenResponse();
    }
}
