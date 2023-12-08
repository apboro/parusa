<?php

namespace App\Models\Ships\Seats;

use App\Models\Dictionaries\TicketGrade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShipSeatCategoryTicketGrade extends Model
{
    protected $table = 'ship_seat_category_has_ticket_grades';
    public $timestamps = false;

    protected $guarded = [];

    public function grade(): HasOne
    {
        return $this->hasOne(TicketGrade::class, 'id', 'ticket_grade_id');
    }
}
