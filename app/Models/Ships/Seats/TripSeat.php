<?php

namespace App\Models\Ships\Seats;

use App\Models\Dictionaries\SeatStatus;
use Illuminate\Database\Eloquent\Model;

class TripSeat extends Model
{
    protected $table='trip_has_seats';
    protected $guarded =[];

    public function status()
    {
        return $this->hasOne(SeatStatus::class, 'id', 'status_id');
    }

    public function seat()
    {
        return $this->hasOne(Seat::class, 'id', 'seat_id');
    }
}
