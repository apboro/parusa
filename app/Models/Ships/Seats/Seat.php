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
}
