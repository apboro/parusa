<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Dictionaries\Role;
use App\Models\Dictionaries\TerminalStatus;
use App\Models\POS\Terminal;
use App\Models\Positions\Position;
use App\Models\User\Helpers\Currents;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use JsonException;

class FrontendController extends Controller
{
    protected array $adminSideRoles = [Role::admin, Role::accountant, Role::piers_manager, Role::office_manager];

    /**
     * Handle requests to frontend index.
     *
     * @param Request $request
     *
     * @return  Response|RedirectResponse
     * @throws JsonException
     */
    public function index(Request $request)
    {
        $current = Currents::get($request);

        // check variants
        $loginVariants = $this->getLoginVariants($current->user());
        $loginVariantsCount = count($loginVariants);

        // if no variants logout with message (has no access to any organizations)
        if ($loginVariantsCount === 0) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return response()->redirectToRoute('login')->with(['message' => __('auth.empty')]);
        }

        // if only one position available set it as current
        if ($loginVariantsCount === 1) {
            $variant = $loginVariants[0];

            /** @var Position $position */
            $position = Position::query()->where('id', $variant['position_id'])->first();
            /** @var ?Role $role */
            $role = $variant['role_id'] ? Role::get($variant['role_id']) : null;
            /** @var ?Terminal $terminal */
            $terminal = $variant['terminal_id'] ? Terminal::query()->where('id', $variant['terminal_id'])->first() : null;

            $current->set($position, $role, $terminal);
        }

        // partner account
        if ($current->position() !== null && !$current->isStaff()) {
            return $this->partnerPage($current, $loginVariantsCount > 1);
        }

        // terminal user role selected
        if ($current->isStaff() && $current->terminal() !== null && $current->role() !== null && $current->role()->matches(Role::terminal)) {
            return $this->terminalPage($current, $loginVariantsCount > 1);
        }

        // admin side
        if ($current->isStaff() && $current->hasRole($this->adminSideRoles)) {
            return $this->adminPage($current, $loginVariantsCount > 1);
        }

        // return select page in all others cases
        return $this->selectPage($current, $loginVariants);
    }

    /**
     * Get available user positions.
     *
     * @param User $user
     *
     * @return  array
     */
    protected function getLoginVariants(User $user): array
    {
        $variants = [];

        // Get all user positions
        $positions = Position::query()->with(['roles', 'partner'])->where('user_id', $user->id)->get();

        // Iterate positions
        foreach ($positions as $position) {
            /** @var Position $position */
            if (!$position->is_staff && $position->hasStatus(PositionAccessStatus::active, 'access_status_id')) {
                // Partner position
                $variants[] = $this->variantRecord($position, null, null);
                continue;
            }
            if ($position->hasStatus(PositionStatus::active)) {
                $adminSideRoles = [];
                $terminalSideRoles = [];
                foreach ($position->roles as $role) {
                    /** @var Role $role */
                    if (in_array($role->id, $this->adminSideRoles, true)) {
                        $adminSideRoles[] = $role;
                    }
                    if ($role->matches(Role::terminal)) {
                        $terminalSideRoles[] = $role;
                    }
                }

                if (!empty($adminSideRoles)) {
                    // Staff with admin role
                    $variants[] = $this->variantRecord($position, $adminSideRoles, null);
                }
                if (!empty($terminalSideRoles)) {
                    // Staff with terminal role
                    $terminals = Terminal::query()->with(['pier', 'pier.info'])
                        ->where('status_id', TerminalStatus::enabled)
                        ->whereHas('staff', function (Builder $query) use ($position) {
                            $query->where('id', $position->id);
                        })
                        ->get();
                    foreach ($terminals as $terminal) {
                        /** @var Terminal $terminal */
                        $variants[] = $this->variantRecord($position, $terminalSideRoles, $terminal);
                    }
                }
            }
        }

        return $variants;
    }

    /**
     * Make login variant record.
     *
     * @param Position $position
     * @param array|null $roles
     * @param Terminal|null $terminal
     *
     * @return  array
     */
    protected function variantRecord(Position $position, ?array $roles, ?Terminal $terminal): array
    {
        if ($position->is_staff) {
            if ($roles !== null && $terminal) {
                /** @var Role $role */
                $role = $roles[0] ?? null;
                $title = "$terminal->name ({$terminal->pier->name})";
            } else {
                $title = __('common.root account caption');
            }
        } else {
            $title = $position->partner->name;
        }

        if(isset($role)) {
            $roleNames = $role->name;
        } else {
            $roleNames = $roles ? implode(', ', array_map(static function (Role $role) {
                return $role->name;
            }, $roles)) : null;
        }

        return [
            'is_staff' => $position->is_staff,
            'position_id' => $position->id,
            'position' => $position->title,
            'organization' => $title,
            'role_id' => $role->id ?? null,
            'role' => $roleNames,
            'terminal_id' => $terminal->id ?? null,
            'terminal' => $terminal ? "$terminal->name ({$terminal->pier->name})" : null,
        ];
    }

    /**
     * Make login select page.
     *
     * @param Currents $current
     * @param array $loginVariants
     *
     * @return  Response
     */
    protected function selectPage(Currents $current, array $loginVariants): Response
    {
        // reset current
        $current->set(null);

        // return select page with variants
        return response()->view('select', ['variants' => $loginVariants])
            ->withCookie($current->positionToCookie())
            ->withCookie($current->roleToCookie())
            ->withCookie($current->terminalToCookie());
    }

    /**
     * Render partner page.
     *
     * @param Currents $current
     * @param bool $canChangePosition
     *
     * @return  Response
     * @throws JsonException
     */
    protected function partnerPage(Currents $current, bool $canChangePosition): Response
    {
        return response()->view('partner', [
            'user' => json_encode([
                'name' => $this->e($current->user()->profile->compactName),
                'organization' => $this->e($current->position()->partner->name),
                'position' => $this->e($current->position()->title),
                'positions' => $canChangePosition,
                'can_reserve' => $current->partner()->profile->can_reserve_tickets,
            ], JSON_THROW_ON_ERROR),
        ])
            ->withCookie($current->positionToCookie())
            ->withCookie($current->roleToCookie())
            ->withCookie($current->terminalToCookie());
    }

    /**
     * Render admin page.
     *
     * @param Currents $current
     * @param bool $canChangePosition
     *
     * @return Response
     * @throws JsonException
     */
    protected function adminPage(Currents $current, bool $canChangePosition): Response
    {
        return response()->view('admin', [
            'user' => json_encode([
                'name' => $this->e($current->user()->profile->compactName),
                'organization' => $this->e(__('common.root account caption')),
                'position' => $this->e($current->position()->title),
                'positions' => $canChangePosition,
                'can_reserve' => false,
            ], JSON_THROW_ON_ERROR),
            'roles' => json_encode($current->position()->roles->map(function (Role $role) {
                return $role->toConst();
            }), JSON_THROW_ON_ERROR),
        ])
            ->withCookie($current->positionToCookie())
            ->withCookie($current->roleToCookie())
            ->withCookie($current->terminalToCookie());
    }

    /**
     * Render terminal page.
     *
     * @param Currents $current
     * @param bool $canChangePosition
     *
     * @return Response
     * @throws JsonException
     */
    protected function terminalPage(Currents $current, bool $canChangePosition): Response
    {
        return response()->view('terminal', [
            'user' => json_encode([
                'name' => $this->e($current->user()->profile->compactName),
                'organization' => $this->e($current->terminal()->name),
                'position' => $this->e($current->position()->title),
                'positions' => $canChangePosition,
                'can_reserve' => false,
            ], JSON_THROW_ON_ERROR),
        ])
            ->withCookie($current->positionToCookie())
            ->withCookie($current->roleToCookie())
            ->withCookie($current->terminalToCookie());
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
        $positionId = $request->input('position_id');
        $roleId = $request->input('role_id');
        $terminalId = $request->input('terminal_id');

        /** @var Position $position */
        $position = Position::query()->where('id', $positionId)->first();
        /** @var ?Role $role */
        $role = $roleId ? Role::get($roleId) : null;
        /** @var ?Terminal $terminal */
        $terminal = $terminalId ? Terminal::query()->where('id', $terminalId)->first() : null;

        $current = Currents::get($request);

        $current->set($position, $role, $terminal);

        return redirect()->back()->withCookies([
            $current->positionToCookie(),
            $current->roleToCookie(),
            $current->terminalToCookie(),
        ]);
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
        $current = Currents::get($request);

        $current->set(null);

        return redirect()->back()->withCookies([
            $current->positionToCookie(),
            $current->roleToCookie(),
            $current->terminalToCookie(),
        ]);
    }

    /**
     * Prepare text to json encoding.
     *
     * @param string|null $text
     *
     * @return  string
     */
    protected function e(?string $text): ?string
    {
        return $text ? str_replace('"', '\\"', $text) : null;
    }
}
