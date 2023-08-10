<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderTicket extends Model
{
    protected $fillable = [
        'provider_id',
        'ticket_id',
    ];

    protected $table = 'provider_ticket';
}
