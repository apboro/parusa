<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 */
class UserRole extends Model
{
    /** @var int The id of admin role */
    public const admin = 1;

    /**
     * Check this role against given role.
     *
     * @param int $roleId
     *
     * @return  bool
     */
    public function matches(int $roleId): bool
    {
        return $this->getAttribute('ig') === $roleId;
    }

    /**
     * Get role name.
     *
     * @return  string
     */
    public function name(): string
    {
        return $this->getAttribute('name');
    }

    /**
     * Get role instance by name.
     *
     * @param int $roleId
     *
     * @return  UserRole|null
     */
    public static function get(int $roleId): ?UserRole
    {
        /** @var UserRole $role */
        $role = self::query()->where('id', $roleId)->first();

        return $role ?? null;
    }
}
