<?php

namespace App\Models\Dictionaries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tariff extends AbstractDictionary
{

    const standard = 1;
    protected $guarded = [];
    protected $table = 'dictionary_tariffs';
    protected $casts = [
        'enabled' => 'boolean',
        'commission' => 'integer',
        'pay_per_hour' => 'string',
        'pay_for_out' => 'string',
        'invisible' => 'boolean',
        'order' => 'int',
    ];

    public function scopeVisible(Builder $query)
    {
        $query->whereNull('invisible')
            ->orWhere('invisible', false);
    }
    public function scopeActive(Builder $query)
    {
        $query->where('enabled', 1);
    }

}
