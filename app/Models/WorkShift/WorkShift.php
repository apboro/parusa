<?php

namespace App\Models\WorkShift;

use App\Models\Dictionaries\Tariff;
use App\Models\POS\Terminal;
use Carbon\Carbon;
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

    public function getPayForTime()
    {
        if ($this->tariff->pay_per_hour) {
            $interval = Carbon::parse($this->start_at)->diff(now());

            return round(($interval->days * 24 + $interval->h + $interval->i / 60), 1) * $this->tariff->pay_per_hour;
        } else {

            return null;
        }
    }

    public function getShiftTotalPay()
    {
        return $this->tariff->pay_for_out + $this->getPayForTime() + $this->pay_commission;
    }


}
