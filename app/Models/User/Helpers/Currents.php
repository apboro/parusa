<?php

namespace App\Models\User\Helpers;

use App\Exceptions\User\BadUserPositionException;
use App\Exceptions\User\BadUserRoleException;
use App\Exceptions\User\BadUserTerminalException;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Dictionaries\Role;
use App\Models\Partner\Partner;
use App\Models\POS\Terminal;
use App\Models\Positions\Position;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class Currents
{
    public const POSITION_COOKIE_NAME = 'current_user_position';
    public const ROLE_COOKIE_NAME = 'current_user_role';
    public const TERMINAL_COOKIE_NAME = 'current_user_terminal';

    /** @var User this helper for. */
    protected User $user;

    /** @var Position|null Current user position. */
    protected ?Position $position = null;

    /** @var Role|null Current user roles. */
    protected ?Role $role = null;

    /** @var Terminal|null Current terminal user logged in (is_staff === true && role === Role::terminal). */
    protected ?Terminal $terminal = null;

    /**
     * Factory.
     *
     * @param Request $request
     *
     * @return  Currents
     */
    public static function get(Request $request): Currents
    {
        /** @var User $user */
        $user = $request->user();

        return $user->current($request);
    }

    /**
     * Create user current state.
     *
     * @param Request $request
     * @param User $user
     *
     * @return  void
     */
    public function __construct(Request $request, User $user)
    {
        $this->user = $user;

        // get user current position
        $positionId = $request->cookie(self::POSITION_COOKIE_NAME);

        // if no position selected just return
        if ($positionId === null) {
            return;
        }

        /** @var Position $position */
        $position = Position::query()->where(['id' => $positionId, 'user_id' => $user->id])->first();
        if (
            ($position !== null) &&
            (
                ($position->is_staff && $position->hasStatus(PositionStatus::active)) ||
                (!$position->is_staff && $position->hasStatus(PositionAccessStatus::active, 'access_status_id'))
            )
        ) {
            $this->position = $position;
        } else {
            return;
        }

        // Non-staff users can have only position
        if (!$position->is_staff) {
            return;
        }

        // get user current role
        $roleId = $request->cookie(self::ROLE_COOKIE_NAME);

        // if no role selected just return
        if ($roleId === null) {
            return;
        }

        /** @var Role $role */
        $role = $position->roles()->where(['id' => $roleId])->first();
        if ($role === null) {
            throw new BadUserRoleException('User can not have this role for current position.');
        }
        $this->role = $role;

        // if role can have terminal retrieve it
        if ($role->matches(Role::terminal)) {
            // get user current role
            $terminalId = $request->cookie(self::TERMINAL_COOKIE_NAME);

            if ($terminalId !== null) {
                /** @var Terminal $terminal */
                $terminal = Terminal::query()
                    ->where('id', $terminalId)
                    ->whereHas('staff', function (Builder $query) use ($positionId) {
                        $query->where('id', $positionId);
                    })
                    ->first();
                if ($terminal === null) {
                    throw new BadUserTerminalException('User can not login using this terminal.');
                }
                $this->terminal = $terminal;
            }
        }
    }

    /**
     * Set current user position and role.
     *
     * @param Position|null $position
     * @param Role|null $role
     * @param Terminal|null $terminal
     *
     * @return  void
     */
    public function set(?Position $position, ?Role $role = null, ?Terminal $terminal = null): void
    {
        if ($position && $position->user_id !== $this->user->id) {
            throw new BadUserPositionException('User can not have this position.');
        }
        $this->position = $position;
        // todo check availability
        $this->role = $role;
        // todo check availability
        $this->terminal = $terminal;
    }

    /**
     * Make position cookie.
     *
     * @return  Cookie
     */
    public function positionToCookie(): Cookie
    {
        return cookie(self::POSITION_COOKIE_NAME, $this->positionId());
    }

    /**
     * Make role cookie.
     *
     * @return  Cookie
     */
    public function roleToCookie(): Cookie
    {
        return cookie(self::ROLE_COOKIE_NAME, $this->roleId());
    }

    /**
     * Make terminal cookie.
     *
     * @return  Cookie
     */
    public function terminalToCookie(): Cookie
    {
        return cookie(self::TERMINAL_COOKIE_NAME, $this->terminalId());
    }

    /**
     * Whether is staff position.
     *
     * @return  bool
     */
    public function isStaff(): bool
    {
        return $this->position() && $this->position()->is_staff;
    }

    /**
     * Whether is representative position.
     *
     * @return  bool
     */
    public function isRepresentative(): bool
    {
        return $this->position() && !$this->position()->is_staff && $this->partnerId() !== null;
    }

    /**
     * Whether is staff position with admin roel.
     *
     * @return  bool
     */
    public function isStaffAdmin(): bool
    {
        return $this->position() && $this->position()->is_staff && $this->role() && $this->role()->matches(Role::admin);
    }

    /**
     * Whether is staff position with terminal role.
     *
     * @return  bool
     */
    public function isStaffTerminal(): bool
    {
        return $this->position() && $this->position()->is_staff && $this->role() && $this->terminalId() && $this->role()->matches(Role::terminal);
    }

    /**
     * Get user id.
     *
     * @return  int|null
     */
    public function userId(): ?int
    {
        return $this->position() ? $this->position()->user_id : null;
    }

    /**
     * Get user.
     *
     * @return  User |null
     */
    public function user(): ?User
    {
        return $this->user ?? null;
    }

    /**
     * Get position id.
     *
     * @return  int|null
     */
    public function positionId(): ?int
    {
        return $this->position() ? $this->position()->id : null;
    }

    /**
     * Get current user position.
     *
     * @return  Position|null
     */
    public function position(): ?Position
    {
        return $this->position;
    }

    /**
     * Get current position role id.
     *
     * @return  int|null
     */
    public function roleId(): ?int
    {
        return $this->role() ? $this->role()->id : null;
    }

    /**
     * Get current position role.
     *
     * @return  Role|null
     */
    public function role(): ?Role
    {
        return $this->role;
    }

    /**
     * Get partner id.
     *
     * @return  int|null
     */
    public function partnerId(): ?int
    {
        return $this->position() ? $this->position()->partner_id : null;
    }

    /**
     * Get partner.
     *
     * @return  Partner|null
     */
    public function partner(): ?Partner
    {
        return $this->position() ? $this->position()->partner : null;
    }

    /**
     * Get terminal id.
     *
     * @return  int|null
     */
    public function terminalId(): ?int
    {
        return $this->terminal() ? $this->terminal()->id : null;
    }

    /**
     * Get terminal.
     *
     * @return  Terminal|null
     */
    public function terminal(): ?Terminal
    {
        return $this->terminal ?? null;
    }
}
