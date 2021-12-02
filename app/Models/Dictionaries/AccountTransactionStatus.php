<?php

namespace App\Models\Dictionaries;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property int $order
 */
class AccountTransactionStatus extends AbstractDictionary
{
    /** @var int The id of accepted status */
    public const accepted = 1;

    /** @var int Default status */
    public const default = self::accepted;

    /** @var string Referenced table name. */
    protected $table = 'dictionary_account_transaction_statuses';
}
