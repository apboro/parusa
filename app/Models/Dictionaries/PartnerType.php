<?php

namespace App\Models\Dictionaries;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property int $order
 */
class PartnerType extends AbstractDictionary
{
    /** @var string Referenced table */
    protected $table = 'dictionary_partner_types';
}
