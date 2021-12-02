<?php

namespace App\Models\Dictionaries;

/**
 * @property int $id
 * @property string $name
 */
class PartnerType extends AbstractDictionary
{
    /** @var string Referenced table */
    protected $table = 'dictionary_partner_types';
}
