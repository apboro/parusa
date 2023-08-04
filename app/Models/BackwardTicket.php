<?php

namespace App\Models;

use App\Models\Positions\PositionOrderingTicket;
use App\Models\Tickets\Ticket;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackwardTicket extends Model
{
    use HasFactory;

    public $fillable = ['main_ticket_id', 'backward_ticket_id'];

}
