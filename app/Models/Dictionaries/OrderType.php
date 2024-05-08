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
    public const promoter_sale = 20;

    /** @var int Витрина на сайте партнера */
    public const partner_site = 2;

    /** @var int Касса */
    public const terminal = 3;

    /** @var int Касса (промоутер) */
    public const terminal_partner = 4;

    /** @var int Витрина на сайте компании «Алые паруса» */
    public const site = 10;

    /** @var int QR-код */
    public const qr_code = 11;

    /** @var int Реферальная ссылка */
    public const referral_link = 12;

    /** @var int Api */
    public const api_sale = 13;

    /** @var int yaga */
    public const yaga_sale = 14;

    public const types_with_sber_payment = [
        self::partner_site,
        self::site,
        self::qr_code,
        self::referral_link,
        self::promoter_sale
    ];
    /** @var string Referenced table name. */
    protected $table = 'dictionary_order_types';
}
