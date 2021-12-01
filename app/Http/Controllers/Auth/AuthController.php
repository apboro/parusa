<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Response;
use App\Models\User\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        return view('login');
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

        if ($user->tokens()->count() === 0) {
            $user->createToken('base_token');
        }

        $request->session()->regenerate();

        $intended = $request->session()->pull('url.intended', RouteServiceProvider::HOME);

        return Response::redirectResponse($intended);
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
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return Response::response('OK');
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
