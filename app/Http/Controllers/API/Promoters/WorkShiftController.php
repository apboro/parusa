<?php

namespace App\Http\Controllers\API\Promoters;

use App\Http\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\WorkShiftStatus;
use App\Models\User\Helpers\Currents;
use App\Models\WorkShift\WorkShift;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WorkShiftController extends Controller
{
    public function save(Request $request)
    {
        $current = Currents::get($request);
        if (!$current->terminalId())
            return APIResponse::error('Доступ запрещён');

        $workShift = WorkShift::create([
            'partner_id' => $request->promoterId,
            'tariff_id' => $request->data['tariff_id'],
            'terminal_id' =>$current->terminalId(),
            'start_at' => now(),
            'status_id' => WorkShiftStatus::active,
        ]);

        return APIResponse::success('Смена открыта', ['start_at' => Carbon::parse($workShift->start_at)->format('Y-m-d H:i:s')]);
    }

    public function pay(Request $request)
    {
        $workShift = WorkShift::query()
            ->with('tariff')
            ->where('partner_id', $request->input('partnerId'))
            ->whereNull('end_at')->first();
        $sumToPay = $request->input('sumToPay');

        $workShift->paid_out = $workShift->paid_out + $sumToPay;
        $workShift->balance = $request->input('totalToPay') - $sumToPay;
        $workShift->save();

        return APIResponse::success('Выплата записана.');
    }

    public function close(Request $request)
    {
        $workShift = WorkShift::query()
            ->where('partner_id', $request->partnerId)
            ->whereNull('end_at')->first();

        if ($payForTime = $workShift->tariff->pay_per_hour) {
            $interval = Carbon::parse($workShift->start_at)->diff(now());
            $payForTime = round(($interval->days * 24 + $interval->h + $interval->i / 60), 1) * $workShift->tariff->pay_per_hour;
        }

        $payTotal = $payForTime + $workShift->tariff->pay_for_out + $workShift->pay_commission;
        $workShift->pay_for_time = $payForTime ?? null;
        $workShift->pay_for_out = $workShift->tariff->pay_for_out;
        $workShift->pay_total = $payTotal;
        $workShift->end_at = now();
        $workShift->save();


        return APIResponse::success('Смена закрыта');
    }
}
