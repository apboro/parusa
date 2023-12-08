<?php

namespace App\Models;

use App\Models\Dictionaries\TicketGrade;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $guarded = [];

    public function grades()
    {
        return $this->belongsToMany(TicketGrade::class, 'grade_has_menus', 'menu_id', 'grade_id');
    }
}
