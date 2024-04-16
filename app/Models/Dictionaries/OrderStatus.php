<?php

namespace App\Models\Dictionaries;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property int $order
 */
class OrderStatus extends AbstractDictionary
{
    /** @var string Referenced table name. */
    protected $table = 'dictionary_order_statuses';

    /**
     * Order has no default status!
     *
     * 1-30 for partners
     * 31-60 for ticket office
     * 61-90 for sites
     * 91-120 qr code link
     * 121-255 - reserved
     */

    public const partner_reserve = 1;
    public const partner_paid = 11;
    public const partner_returned = 21;
    public const partner_partial_returned = 22;
    public const partner_reserve_canceled = 23;
    public const promoter_wait_for_pay = 26;
    public const promoter_confirmed = 27;
    public const promoter_paid = 28;
    public const promoter_canceled = 29;

    public const terminal_creating = 31;
    public const terminal_creating_from_reserve = 32;
    public const terminal_wait_for_pay = 41;
    public const terminal_wait_for_pay_from_reserve = 42;
    public const terminal_finishing = 45;
    public const terminal_paid = 50;
    public const terminal_canceled = 51;
    public const terminal_wait_for_return = 54;
    public const terminal_returned = 52;
    public const terminal_partial_returned = 53;

    public const showcase_creating = 61;
    public const showcase_wait_for_pay = 62;
    public const showcase_confirmed = 72;
    public const showcase_paid = 71;
    public const showcase_returned = 81;
    public const showcase_partial_returned = 82;
    public const showcase_canceled = 90;

    public const done = 100;
    public const api_reserved = 101;
    public const api_confirmed = 105;
    public const api_canceled = 110;
    public const api_returned = 115;
    public const yaga_reserved = 120;
    public const yaga_confirmed = 125;
    public const yaga_canceled = 130;

    public const order_returnable_statuses = [
        self::partner_paid,
        self::partner_partial_returned,
        self::terminal_paid,
        self::terminal_partial_returned,
        self::terminal_wait_for_return,
        self::showcase_paid,
        self::showcase_partial_returned,
        self::api_confirmed,
        self::yaga_confirmed,
        self::promoter_paid
    ];

    public const order_printable_statuses = [
        self::partner_paid,
        self::partner_partial_returned,
        self::terminal_paid,
        self::terminal_partial_returned,
        self::showcase_paid,
        self::showcase_partial_returned,
        self::promoter_paid,
        self::api_confirmed,
        self::yaga_confirmed
    ];

    public const partner_commission_pay_statuses = [
        self::partner_paid,
        self::terminal_paid,
        self::showcase_paid,
        self::promoter_paid,
        self::api_confirmed,
        self::yaga_confirmed,

    ];

    public const partial_returned_statuses = [
        self::partner_partial_returned,
        self::terminal_partial_returned,
        self::showcase_partial_returned
    ];

    public const order_had_paid_statuses = [
        self::partner_paid,
        self::partner_returned,
        self::partner_partial_returned,
        self::terminal_finishing,
        self::terminal_paid,
        self::terminal_wait_for_return,
        self::terminal_returned,
        self::terminal_partial_returned,
        self::showcase_paid,
        self::showcase_returned,
        self::showcase_partial_returned,
        self::promoter_paid,
        self::api_confirmed,
        self::yaga_confirmed,
    ];

    public const order_reserved_statuses = [
        self::partner_reserve,
    ];

    public const terminal_processing_statuses = [
        self::terminal_creating,
        self::terminal_creating_from_reserve,
        self::terminal_wait_for_pay,
        self::terminal_wait_for_pay_from_reserve,
        self::terminal_finishing,
    ];

    public const sberpay_statuses = [
        self::showcase_creating,
        self::showcase_paid,
        self::showcase_confirmed,
        self::showcase_wait_for_pay,
        self::promoter_wait_for_pay,
        self::promoter_confirmed,
        self::promoter_paid,
    ];
    public const yaga_statuses = [
        self::yaga_reserved,
        self::yaga_confirmed,
        self::yaga_canceled,
    ];
}
