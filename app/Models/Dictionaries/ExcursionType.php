<?php

namespace App\Models\Dictionaries;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcursionType extends Model
{
    const water = 10;
    const earth = 20;

    const fire = 30;
    const air = 40;

    const legs = 60;

    const standUp = 70;

    const combined = 50;

    protected $table = 'dictionary_excursion_types';

}
