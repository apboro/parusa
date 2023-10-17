<?php

namespace App\Models\Dictionaries;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tariff extends AbstractDictionary
{
    use HasFactory;

    protected $table = 'dictionary_tariffs';
}
