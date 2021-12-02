<?php

namespace App\Models\Dictionaries;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property int $order
 * @property int $sign
 */
class AccountTransactionType extends AbstractDictionary
{
    /** @var int The id of partner account refill. Not final. */
    public const account_refill = 1;

    /** @var int The id of partner account refill by invoice. Final. */
    public const account_refill_invoice = 2;

    /** @var int The id of tickets buys fee. Final. */
    public const tickets_buy = 50;

    /** @var int The id of refund for tickets return. Final. */
    public const tickets_buy_return = 51;

    /** @var int The id of refund commission for tickets sell. Final. */
    public const tickets_sell_commission = 100;

    /** @var int The id of commission return on tickets return. Final. */
    public const tickets_sell_commission_return = 101;


    /** @var string Referenced table name. */
    protected $table = 'dictionary_account_transaction_types';
}
