<?php

namespace App\Models\Dictionaries;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property int $order
 */
class TripStatus extends AbstractDictionary
{
    /** @var int The id of regular status */
    public const regular = 1;

    /** @var int The id of processing status */
    public const processing = 2;

    /** @var int The id of finished status */
    public const finished = 3;

    /** @var int The id of cancelled status */
    public const cancelled = 4;

    /** @var int Default status */
    public const default = self::regular;

    /** @var string Referenced table name. */
    protected $table = 'dictionary_trip_statuses';
}
