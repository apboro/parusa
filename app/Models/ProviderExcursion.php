<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $provider_excursion_status
 */
class ProviderExcursion extends Model
{
    protected $fillable = [
        'provider_id',
        'excursion_id',
        'provider_excursion_id',
        'provider_excursion_status',
    ];

    protected $table = 'provider_excursion';
}
