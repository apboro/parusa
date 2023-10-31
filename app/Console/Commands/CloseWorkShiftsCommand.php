<?php

namespace App\Console\Commands;

use App\Models\WorkShift\WorkShift;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CloseWorkShiftsCommand extends Command
{
    protected $signature = 'close:work-shifts';

    protected $description = 'Закрыть открытые смены промоутеров, деньги записать на баланс';

    public function handle(): void
    {
        $openedShifts = WorkShift::query()->whereNull('end_at')->get();

        foreach ($openedShifts as $shift) {
            $payTotal = $shift->getShiftTotalPay();
            $shift->pay_for_time = $shift->getPayForTime();
            $shift->pay_for_out = $shift->tariff->pay_for_out;
            $shift->pay_total = $payTotal;
            $shift->end_at = now();
            $shift->balance = $payTotal - $shift->paid_out;
            $shift->save();
        }
    }
}
