<?php

namespace App\Models\Ships\Seats;

use App\Models\Dictionaries\AbstractDictionary;

class SeatCategory extends AbstractDictionary
{
    protected $table = 'dictionary_seat_categories';
    protected $hidden = ['created_at', 'updated_at', 'order', 'enabled'];

    protected $guarded = [];
    protected $casts = [
        'enabled' => 'boolean',
        'locked' => 'boolean',
        'table_seat' => 'boolean',
        'order' => 'int',
    ];
}
