<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdditionalDataTicket extends Model
{
    protected $fillable = [
        'provider_id',
        'ticket_id',
    ];

    protected $table = 'additional_data_tickets';

    public $timestamps = false;
}
