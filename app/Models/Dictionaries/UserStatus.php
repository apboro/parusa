<?php

namespace App\Models\Dictionaries;

class UserStatus extends AbstractDictionaryItem
{
    /** @var int The id of blocked status */
    public const blocked = 1;

    /** @var int The id of active status */
    public const active = 2;

    /** @var string Referenced table name. */
    protected $table = 'dictionary_user_statuses';

    /** @var bool Disable timestamps. */
    public $timestamps = false;
}
