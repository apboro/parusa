<?php

namespace App\Models;

use App\Models\Piers\Pier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripStop extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['pier'];

    protected $casts = [
        'start_at' => 'datetime',
    ];

    public function pier(): BelongsTo
    {
        return $this->belongsTo(Pier::class, 'stop_pier_id', 'id');
    }
}
