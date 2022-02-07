<?php

namespace App\Models\Dictionaries;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property int $order
 */
class OrderType extends AbstractDictionary
{
    /** @var int Личный кабинет партнёра */
    public const partner_sale = 1;

    /** @var int Витрина на сайте партнера */
    public const partner_site = 2;


    /** @var int Витрина на сайте компании «Алые паруса» */
    public const site = 10;

    /** @var int QR-код */
    public const qr_code = 11;

    /** @var string Referenced table name. */
    protected $table = 'dictionary_order_types';
}
