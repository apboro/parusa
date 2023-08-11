<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdditionalDataOrder extends Model
{
    protected $fillable = [
        'provider_id',
        'order_id',
        'provider_order_id',
        'provider_order_uuid',
    ];

    protected $table = 'additional_data_orders';

    public $timestamps = false;
}
