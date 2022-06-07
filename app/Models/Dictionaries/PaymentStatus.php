<?php

namespace App\Models\Dictionaries;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property int $order
 */
class PaymentStatus extends AbstractDictionary
{
    /** @var int The id of sale status */
    public const sale = 1;

    /** @var int The id of return status */
    public const return = 2;

    /** @var string Referenced table name. */
    protected $table = 'payment_statuses';
}
