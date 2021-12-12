<?php

namespace App\Models\Dictionaries;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property int $order
 */
class TripDiscountStatus extends AbstractDictionary
{
    /** @var int The id of regular status */
    public const enabled = 1;

    /** @var int The id of processing status */
    public const disabled = 2;

    /** @var int Default status */
    public const default = self::enabled;

    /** @var string Referenced table name. */
    protected $table = 'dictionary_trip_discount_statuses';
}
