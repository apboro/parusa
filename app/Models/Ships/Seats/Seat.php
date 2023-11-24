<?php

namespace App\Models\Ships\Seats;

use Illuminate\Database\Eloquent\Model;

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
        return TripSeat::query()->where('trip_id', $tripId)->where('seat_number', $this->seat_number)->first()?->status;
    }
}
