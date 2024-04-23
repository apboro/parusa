<?php

namespace App\Models\WorkShift;

use App\Http\APIResponse;
use App\Models\Partner\Partner;
use Request;
use App\Models\Dictionaries\Tariff;
use App\Models\POS\Terminal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

/**
 * @property mixed $promoter
 * @property mixed $sales_total
 */
class WorkShift extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }

    public function promoter()
    {
        return $this->belongsTo(Partner::class, 'partner_id', 'id');
    }

    public function terminal()
    {
        return $this->belongsTo(Terminal::class, 'terminal_id', 'id');
    }

    public function getPayForTime(): int
    {
        if ($this->tariff->pay_per_hour) {
            return $this->getWorkingHours() * $this->tariff->pay_per_hour;
        } else {
            return 0;
        }
    }

    public function getWorkingHours(): int
    {
        $interval = Carbon::parse($this->start_at)->diff($this->end_at ? Carbon::parse($this->end_at) : now());

        return $interval->days * 24 + $interval->h + $interval->i / 60;
    }

    public function getShiftTotalPay()
    {
        return $this->tariff->pay_for_out + $this->getPayForTime() + $this->pay_commission;
    }

    public function getCurrentCommission()
    {
        return $this->tariff->commission + $this->commission_delta;
    }

}
