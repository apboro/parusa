<?php

namespace App\Http\Middleware;

use App\Http\APIResponse;
use App\Http\Response;
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

        foreach ($rules as $rule) {
            $set = explode('_', $rule, 2);
            if ($set[0] === 'partner' && $current->isRepresentative()) {
                return $next($request);
            }
            if ($set[0] === 'staff' && !isset($set[1]) && $current->isStaff()) {
                return $next($request);
            }
            if ($set[0] === 'staff' && isset($set[1]) && $set[1] === 'admin' && $current->isStaffAdmin()) {
                return $next($request);
            }
            if ($set[0] === 'staff' && isset($set[1]) && $set[1] === 'terminal' && $current->isStaffTerminal()) {
                return $next($request);
            }
            if ($set[0] === 'staff' && isset($set[1]) && $set[1] === 'office_manager' && $current->isStaffOfficeManager()) {
                return $next($request);
            }
            if ($set[0] === 'staff' && isset($set[1]) && $set[1] === 'piers_manager' && $current->isStaffPiersManager()) {
                return $next($request);
            }
            if ($set[0] === 'staff' && isset($set[1]) && $set[1] === 'accountant' && $current->isStaffAccountant()) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return APIResponse::forbidden();
        }

        return Response::forbiddenResponse();
    }
}
