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
     * 61-90 for showcase
     * 91-120 qr code link
     * 121-255 - reserved
     */

    public const partner_reserve = 1;
    public const partner_paid = 11;
    public const partner_returned = 21;
    public const partner_reserve_canceled = 22;
    public const partner_paid_single = 25;
    public const promoter_wait_for_pay = 26;
    public const promoter_confirmed = 27;
    public const promoter_paid = 28;
    public const terminal_creating = 31;
    public const terminal_creating_from_reserve = 32;
    public const terminal_wait_for_pay = 41;
    public const terminal_wait_for_pay_from_reserve = 42;
    public const terminal_finishing = 45;
    public const terminal_paid = 50;
    public const terminal_wait_for_return = 53;
    public const terminal_returned = 52;
    public const terminal_canceled = 51;
    public const terminal_paid_single = 55;
    public const api_reserved = 100;
    public const api_confirmed = 105;
    public const api_canceled = 110;
    public const api_returned = 115;
    public const yaga_reserved = 120;
    public const yaga_confirmed = 125;
    public const yaga_canceled = 130;

    public const showcase_creating = 61;
    public const showcase_wait_for_pay = 62;
    public const showcase_paid = 71;
    public const showcase_returned = 81;
    public const showcase_canceled = 90;
    public const showcase_paid_single = 95;

    public const used = 254;
    public const expired = 255;

    public const ticket_countable_statuses = [
        self::partner_reserve,
        self::partner_paid,
        self::terminal_creating,
        self::terminal_wait_for_pay,
        self::terminal_finishing,
        self::terminal_paid,
        self::terminal_wait_for_return,
        self::showcase_creating,
        self::showcase_wait_for_pay,
        self::showcase_paid,
        self::used,
        self::promoter_wait_for_pay,
        self::promoter_paid,
        self::promoter_confirmed,
        self::api_reserved,
        self::api_confirmed,
        self::yaga_confirmed,
        self::yaga_reserved
    ];

    public const ticket_printable_statuses = [
        self::partner_paid,
        self::terminal_finishing,
        self::terminal_paid,
        self::showcase_paid,
        self::showcase_paid_single,
        self::terminal_paid_single,
        self::partner_paid_single,
        self::promoter_paid,
        self::api_confirmed,
        self::yaga_confirmed
    ];

    public const ticket_paid_statuses = [
        self::partner_paid,
        self::terminal_paid,
        self::showcase_paid,
        self::showcase_paid_single,
        self::terminal_paid_single,
        self::partner_paid_single,
        self::promoter_paid,
        self::api_confirmed,
        self::yaga_confirmed,
    ];

    public const ticket_had_paid_statuses = [
        self::partner_paid,
        self::partner_returned,
        self::terminal_paid,
        self::terminal_returned,
        self::showcase_paid,
        self::showcase_returned,
        self::showcase_paid_single,
        self::terminal_paid_single,
        self::partner_paid_single,
        self::api_confirmed,
        self::yaga_confirmed,
        self::promoter_paid
    ];

    public const ticket_reserved_statuses = [
        self::partner_reserve,
        self::yaga_reserved
    ];

    public const ticket_returnable_statuses = [
        self::showcase_paid,
        self::partner_paid,
        self::terminal_paid,
        self::terminal_wait_for_return,
        self::showcase_paid_single,
        self::terminal_paid_single,
        self::partner_paid_single,
        self::used,
        self::api_confirmed,
        self::yaga_confirmed
    ];

    public const ticket_cancelled_statuses = [
        self::partner_reserve_canceled,
        self::terminal_canceled,
        self::showcase_canceled,
        self::api_canceled,
        self::yaga_canceled
    ];
    public const ticket_single_statuses = [
        self::showcase_paid_single,
        self::terminal_paid_single,
        self::partner_paid_single,
    ];
}
