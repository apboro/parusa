<?php

namespace App\Models\Dictionaries;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    public const neva_travel = 10;

    public const city_tours_spb = 20;

    protected $fillable = [
        'name',
        'status',
        'service'
    ];

    protected $table = 'dictionary_providers';
}
