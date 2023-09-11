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
    /** @var int Администратор */
    public const admin = 1;

    /** @var int Кассир */
    public const terminal = 2;

    /** @var int Менеджер в офисе */
    public const office_manager = 3;

    /** @var int Управляющий причалом */
    public const piers_manager = 4;

    /** @var int Бухгалтер */
    public const accountant = 5;

    /** @var int Контролёр */
    public const controller = 10;

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

    public function toConst(): string
    {
        return match ($this->id) {
            self::admin => 'admin',
            self::terminal => 'terminal',
            self::office_manager => 'office_manager',
            self::piers_manager => 'piers_manager',
            self::accountant => 'accountant',
            self::controller => 'controller',
            default => 'unknown',
        };
    }
}
