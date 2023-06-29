<?php

namespace App\Models\Dictionaries;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property int $order
 */
class PromoCodeType extends AbstractDictionary
{
    /** @var int The id of fixed type */
    public const fixed = 1;

    /** @var int Default type */
    public const default = self::fixed;

    /** @var string Referenced table name. */
    protected $table = 'dictionary_promo_code_types';
}
