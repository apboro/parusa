<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdditionalDataTrip extends Model
{
    protected $fillable = [
        'provider_id',
        'trip_id',
        'provider_trip_id',
        'provider_price_id',
    ];

    protected $table = 'additional_data_trips';

    public $timestamps = false;
}
