<?php

namespace App\Models\WorkShift;

use App\Models\Dictionaries\Tariff;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkShift extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }
}
