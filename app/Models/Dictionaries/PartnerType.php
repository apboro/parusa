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
    public const hotel = 1002;
    public const tour_firm = 1001;
    public const promoter = 1003;
    public const horeka = 1004;
    public const taxi = 1005;

    /** @var string Referenced table */
    protected $table = 'dictionary_partner_types';
}
