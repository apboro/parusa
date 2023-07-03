<?php

namespace App\Models\Dictionaries;

/**
 * @property int $id
 * @property string $name
 */
class HitSource extends AbstractDictionary
{
    /** @var int The id of admin */
    public const admin = 1;

    /** @var int The id of terminal */
    public const terminal = 2;

    /** @var int The id of partner */
    public const partner = 3;

    /** @var int The id of showcase */
    public const showcase = 4;

    /** @var int The id of checkout */
    public const checkout = 5;

    /** @var int The id of qrlink */
    public const qrlink = 6;

    /** @var int The id of referal */
    public const referal = 7;

    /** @var int The id of crm */
    public const crm = null;

    /** @var string Referenced table name. */
    protected $table = 'dictionary_hit_sources';
}
