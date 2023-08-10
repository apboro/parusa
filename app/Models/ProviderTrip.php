<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderTrip extends Model
{
    protected $fillable = [
        'provider_id',
        'trip_id',
        'provider_trip_id',
        'provider_price_id',
    ];

    protected $table = 'provider_trip';
}
