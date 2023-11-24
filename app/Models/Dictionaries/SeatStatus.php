<?php

namespace App\Models\Dictionaries;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatStatus extends Model
{
    protected $table = 'dictionary_seat_statuses';
    public const vacant = 1;
    public const reserve = 5;
    public const occupied = 10;

}
