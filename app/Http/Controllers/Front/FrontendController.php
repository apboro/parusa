<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Dictionaries\Role;
use App\Models\Positions\Position;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class FrontendController extends Controller
{
    /**
     * Handle requests to frontend index.
     *
     * @param Request $request
     *
     * @return Response|RedirectResponse
     */
    public function frontend(Request $request): Response|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $current = $user->current($request);

        if ($current->position() === null) {
            $positions = $this->getUserActivePositions($user);

            if ($positions->count() === 0) {
                // has no access to any organizations
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return response()->redirectToRoute('login')->withErrors(['message' => __('No access to any organizations')]);
            }

            if ($positions->count() === 1) {
                $current->setPosition($positions->first());
            } else {
                $current->setPosition(null);
                return response()->view('select', [
                    'positions' => $positions->map(static function (Position $position) {
                        return [
                            'id' => $position->id,
                            'title' => $position->title,
                            'partner' => $position->partner ? $position->partner->name : null,
                            'roles' => $position->roles->map(static function (Role $role) {
                                return [
                                    'id' => $role->id,
                                    'name' => $role->name,
                                ];
                            }),
                        ];
                    }),
                ])->withCookie($current->positionToCookie());
            }
        }

        if ($current->position()->is_staff) {
            return response()->view('admin')->withCookie($current->positionToCookie());
        }

        return response()->view('partner')->withCookie($current->positionToCookie());
    }

    /**
     * Handle position select request.
     *
     * @param Request $request
     *
     * @return  RedirectResponse
     */
    public function select(Request $request): RedirectResponse
    {
        $positionId = $request->input('position');

        /** @var Position $position */
        $position = Position::query()->where('id', $positionId)->first();

        /** @var User $user */
        $user = $request->user();

        $user->current($request)->setPosition($position);

        return redirect()->back()->withCookies([$user->current()->positionToCookie()]);
    }

    /**
     * Get available user positions.
     *
     * @param User $user
     *
     * @return  Collection
     */
    protected function getUserActivePositions(User $user): Collection
    {
        return Position::query()->with(['roles', 'partner'])->where('user_id', $user->id)->get()->reject(function (Position $position) {
            return ($position->is_staff && !$position->hasStatus(PositionStatus::active)) ||
                (!$position->is_staff && !$position->hasStatus(PositionAccessStatus::active, 'access_status_id'));
        });
    }
}
