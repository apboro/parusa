<?php

namespace App\Models\Integration;

use Illuminate\Database\Eloquent\Model;

class AdditionalDataTrip extends Model
{
    protected $guarded = [];

    protected $casts =[
      'with_seats' => 'boolean',
    ];

    protected $table = 'additional_data_trips';

    public $timestamps = false;
}
