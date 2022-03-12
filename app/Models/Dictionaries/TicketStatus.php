<?php

namespace App\Models\Dictionaries;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property int $order
 */
class TicketStatus extends AbstractDictionary
{
    /** @var string Referenced table name. */
    protected $table = 'dictionary_ticket_statuses';

    /**
     * Tickets has no default status!
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
    public const terminal_finishing = 45;
    public const terminal_paid = 50;
    public const terminal_canceled = 51;


    /** @var int The ticket had expired */
    public const expired = 255;

    public const countable = [
        self::partner_reserve,
        self::partner_paid,
        self::terminal_creating,
        self::terminal_wait_for_pay,
        self::terminal_finishing,
        self::terminal_paid,
    ];

    public const ticket_sold_statuses = [
        self::partner_paid,
        self::terminal_paid,
    ];

    public const ticket_reserved_statuses = [
        self::partner_reserve,
    ];
}
