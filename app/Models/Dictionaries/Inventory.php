<?php

namespace App\Models\Dictionaries;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends AbstractDictionary
{
    use HasFactory;

    protected $table = 'dictionary_inventory';
    protected $guarded = [];
}
