<?php

namespace App\Models\Dictionaries;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcursionType extends Model
{
    use HasFactory;
    const water = 10;
    const earth = 20;

    const fire = 30;
    const air = 40;

    const combined = 50;

    protected $table = 'dictionary_excursion_types';

}
