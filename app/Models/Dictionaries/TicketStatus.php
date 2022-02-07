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
    /**
     * Tickets has no default statuses!
     */

    /** @var string Referenced table name. */
    protected $table = 'dictionary_ticket_statuses';

    /** @var int The newly created ticket */
    public const creating = 1;

    /** @var int The ticket in reserve by partner */
    public const partner_reserved = 2;

    /** @var int The ticket was paid by partner*/
    public const partner_payed = 3;

    /** @var int The ticket was returner */
    public const partner_returned = 4;

    /** @var int The ticket had expired */
    public const expired = 255;

    public const ticket_sold_statuses = [
        self::partner_payed,
    ];

    public const ticket_reserved_statuses = [
        self::partner_reserved,
    ];
}
