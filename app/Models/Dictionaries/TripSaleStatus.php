<?php

namespace App\Models\Dictionaries;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property int $order
 */
class TripSaleStatus extends AbstractDictionary
{
    /** @var int The id of selling status */
    public const selling = 1;

    /** @var int The id of closed manually status */
    public const closed_manually = 2;

    /** @var int The id of closed automatically status */
    public const closed_automatically = 3;

    /** @var int Default status */
    public const default = self::selling;

    /** @var string Referenced table name. */
    protected $table = 'dictionary_trip_sale_statuses';
}
