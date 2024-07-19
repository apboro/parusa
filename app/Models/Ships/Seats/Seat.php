<?php

namespace App\Models\Ships\Seats;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seat extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function category()
    {
        return $this->hasOne(SeatCategory::class,'id', 'seat_category_id');
    }

    public function status(int $tripId)
    {
        return TripSeat::query()->where('trip_id', $tripId)->where('seat_id', $this->id)->first()?->status;
    }

    public function tripSeat(): HasMany
    {
        return $this->hasMany(TripSeat::class, 'seat_id', 'id');
    }
}
