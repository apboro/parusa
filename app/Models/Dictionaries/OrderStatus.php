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

    public const terminal_creating = 31;
    public const terminal_wait_for_pay = 41;
    public const terminal_printing = 45;
    public const terminal_paid = 50;

    public const partner_commission_pay_statuses = [
        self::partner_paid,
    ];

    public const order_paid_statuses = [
        self::partner_paid,
        self::partner_returned,
    ];

    public const order_reserved_statuses = [
        self::partner_reserve,
    ];

    public const terminal_processing_statuses = [
        self::terminal_creating,
        self::terminal_wait_for_pay,
        self::terminal_printing,
    ];
}
