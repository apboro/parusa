<?php

namespace App\Models\User\Helpers;

use App\Exceptions\User\BadUserPositionException;
use App\Exceptions\User\BadUserRoleException;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Dictionaries\Role;
use App\Models\Partner\Partner;
use App\Models\Positions\Position;
use App\Models\User\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class Currents
{
    public const POSITION_COOKIE_NAME = 'current_user_position';
    public const ROLE_COOKIE_NAME = 'current_user_role';

    /** @var User this helper for. */
    protected User $user;

    /** @var Position|null Current user position. */
    protected ?Position $position = null;

    /** @var Role|null Current user roles. */
    protected ?Role $role = null;

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
    }

    /**
     * Set current user position and role.
     *
     * @param Position|null $position
     * @param Role|null $role
     *
     * @return  void
     */
    public function set(?Position $position, ?Role $role = null): void
    {
        if ($position && $position->user_id !== $this->user->id) {
            throw new BadUserPositionException('User can not have this position.');
        }
        $this->position = $position;


        $this->role = $role;
    }

    /**
     * Make position cookie.
     *
     * @return  Cookie
     */
    public function positionToCookie(): Cookie
    {
        return cookie(self::POSITION_COOKIE_NAME, $this->position() ? $this->position()->id : null);
    }

    /**
     * Make role cookie.
     *
     * @return  Cookie
     */
    public function roleToCookie(): Cookie
    {
        return cookie(self::ROLE_COOKIE_NAME, $this->role() ? $this->role()->id : null);
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
     * Get user id.
     *
     * @return  int|null
     */
    public function userId(): ?int
    {
        return $this->position() ? $this->position()->user_id : null;
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
     * Get user.
     *
     * @return  User |null
     */
    public function user(): ?User
    {
        return $this->user ?? null;
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
}
