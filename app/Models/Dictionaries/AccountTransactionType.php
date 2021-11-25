<?php

namespace App\Models\Dictionaries;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $sign
 */
class AccountTransactionType extends Model
{
    /** @var string Referenced table name. */
    protected $table = 'dictionary_account_transaction_types';

    /** @var bool Disable timestamps. */
    public $timestamps = false;
}
