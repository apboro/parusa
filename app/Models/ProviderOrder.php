<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderOrder extends Model
{
    protected $fillable = [
        'provider_id',
        'order_id',
        'provider_order_id',
        'provider_order_uuid',
    ];
    protected $table = 'provider_order';
}
