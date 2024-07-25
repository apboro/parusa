<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $guarded = [];
    protected $table = 'dictionary_cities';
    protected $casts = [
        'enabled' => 'boolean',
    ];

    public const spb = 1;
    public const kazan = 2;
}
