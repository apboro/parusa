<?php

namespace App\Models\Dictionaries;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property int $order
 */
class Role extends AbstractDictionary
{
    /** @var int The id of admin role. */
    public const admin = 1;

    /** @var string Referenced table name. */
    protected $table = 'dictionary_roles';

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
}
