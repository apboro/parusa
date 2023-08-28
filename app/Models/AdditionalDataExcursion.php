<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $provider_excursion_status
 * @property int|mixed $excursion_id
 * @property mixed $provider_excursion_id
 * @property int|mixed $provider_id
 */
class AdditionalDataExcursion extends Model
{
    protected $fillable = [
        'provider_id',
        'excursion_id',
        'provider_excursion_id',
        'provider_excursion_status',
    ];

    protected $table = 'additional_data_excursions';

    public $timestamps = false;
}
