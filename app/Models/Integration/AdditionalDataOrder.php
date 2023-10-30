<?php

namespace App\Models\Integration;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|mixed $provider_id
 * @property mixed $provider_order_id
 * @property int|mixed $order_id
 */
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
