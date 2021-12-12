<?php

namespace App\Models\Dictionaries;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property int $order
 */
class ShipType extends AbstractDictionary
{
    /** @var string Referenced table name. */
    protected $table = 'dictionary_ship_types';
}
