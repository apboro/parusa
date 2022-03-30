<?php

namespace App\Models\Tickets;

use App\Models\Model;
use Carbon\Carbon;

/**
 * @property int $id
 * @property int $ticket_id
 * @property string $reason
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TicketReturn extends Model
{
    /** @var string[] Fillable attributes. */
    protected $fillable = ['ticket_id', 'reason'];
}
