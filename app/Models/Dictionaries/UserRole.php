<?php

namespace App\Models\Dictionaries;

/**
 * @property int $id
 * @property string $name
 */
class UserRole extends AbstractDictionaryItem
{
    /** @var int The id of admin role. */
    public const admin = 1;

    /** @var string Referenced table name. */
    protected $table = 'dictionary_user_roles';

    /** @var bool Disable timestamps. */
    public $timestamps = false;

    /**
     * Match this role against given.
     *
     * @param int $roleId
     *
     * @return  bool
     */
    public function matches(int $roleId): bool
    {
        return $this->getAttribute('id') === $roleId;
    }

    /**
     * Get role display name.
     *
     * @return  string
     */
    public function name(): string
    {
        return $this->getAttribute('name');
    }
}
