<?php

namespace App\Models\Dictionaries;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property int $order
 */
class TerminalStatus extends AbstractDictionary
{
    /** @var int Работает */
    public const enabled = 1;

    /** @var int Не работает */
    public const disabled = 2;

    /** @var int Default status */
    public const default = self::enabled;

    /** @var string Referenced table name. */
    protected $table = 'dictionary_terminal_statuses';
}
