<?php

namespace App\Http\Controllers\Auth;

use App\Http\APIResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Response;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Positions\Position;
use App\Models\User\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Render login form.
     *
     * @return  View
     */
    public function form(): View
    {
        $message = Session::get('message');

        return view('login', ['message' => $message]);
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param LoginRequest $request
     *
     * @return  JsonResponse
     *
     * @throws  ValidationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        /** @var User $user */
        $user = $request->user();

        $positionsCount = Position::query()->where(['is_staff' => false, 'user_id' => $user->id, 'access_status_id' => PositionAccessStatus::active])->count();
        $staffCount = Position::query()->where(['is_staff' => true, 'user_id' => $user->id, 'status_id' => PositionStatus::active])->count();
        $rolesCount = 0;
        if ($staffCount > 0) {
            $positions = Position::query()->withCount(['roles'])->where(['is_staff' => true, 'user_id' => $user->id, 'status_id' => PositionStatus::active])->get();
            foreach ($positions as $position) {
                /** @var Position $position */
                $rolesCount += $position->getAttribute('roles_count');
            }
        }

        if ($positionsCount + $rolesCount === 0) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            throw ValidationException::withMessages([
                'login' => __('auth.empty'),
            ]);
        }

        if ($user->tokens()->count() === 0) {
            $user->createToken('base_token');
        }

        $request->session()->regenerate();

        $intended = $request->session()->pull('url.intended', RouteServiceProvider::HOME);

        return APIResponse::redirect($intended);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $current = $user->current($request);
        $current->set(null);

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return Response::response('OK')
            ->withCookie($current->positionToCookie())
            ->withCookie($current->roleToCookie())
            ->withCookie($current->terminalToCookie());
    }

    /**
     * Get fresh token.
     *
     * @return  JsonResponse
     */
    public function token(): JsonResponse
    {
        return response()->json(['token' => csrf_token()]);
    }
}
