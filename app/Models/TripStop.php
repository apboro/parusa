<?php

namespace App\Models;

use App\Models\Piers\Pier;
use App\Models\Sails\Trip;
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

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id', 'id');
    }
}
