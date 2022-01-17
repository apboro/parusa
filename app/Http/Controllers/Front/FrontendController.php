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

        $positions = $this->getUserActivePositions($user);
        $positionsCount = $positions->count();

        if ($current->position() === null) {
            if ($positionsCount === 0) {
                // has no access to any organizations
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return response()->redirectToRoute('login')->withErrors(['message' => __('No access to any organizations')]);
            }

            if ($positionsCount === 1) {
                $current->setPosition($positions->first());
            } else {
                $current->setPosition(null);
                return response()->view('select', [
                    'positions' => $positions->map(static function (Position $position) {
                        return [
                            'id' => $position->id,
                            'is_staff' => $position->is_staff,
                            'title' => $position->title,
                            'partner' => $position->is_staff ? __('common.root organization') : $position->partner->name,
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
            return response()->view('admin', [
                'user' => [
                    'name' => $user->profile->compactName,
                    'organization' => __('common.root organization'),
                    'positions' => $positionsCount > 1,
                ],
            ])->withCookie($current->positionToCookie());
        }

        return response()->view('partner', [
            'user' => [
                'name' => $user->profile->compactName,
                'organization' => $current->position()->partner->name,
                'positions' => $positionsCount > 1,
            ],
        ])->withCookie($current->positionToCookie());
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
     * Handle position change request.
     *
     * @param Request $request
     *
     * @return  RedirectResponse
     */
    public function change(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->current($request)->setPosition(null);

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
