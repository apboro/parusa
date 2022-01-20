<?php

namespace App\Models\Dictionaries;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property int $order
 */
class TicketGrade extends AbstractDictionary
{
    /** @var string Referenced table */
    protected $table = 'dictionary_ticket_grades';

    /** @var string[] Attributes casting */
    protected $casts = [
        'locked' => 'bool',
    ];
}
