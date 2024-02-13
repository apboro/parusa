<?php

namespace App\Models\Tickets;

use Illuminate\Database\Eloquent\Model;

class BackwardTicket extends Model
{

    public $fillable = ['main_ticket_id', 'backward_ticket_id'];

    public $timestamps = false;

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'backward_ticket_id', 'id');
    }

}
