<?php

namespace App\Models\Dictionaries;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    public const scarlet_sails = 5;
    public const neva_travel = 10;

    public const city_tour = 20;

    public const astra_marine = 30;

    protected $fillable = [
        'name',
        'status',
        'service'
    ];

    protected $table = 'dictionary_providers';

    public $timestamps = false;
}
