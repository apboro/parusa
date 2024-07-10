<?php

namespace App\Http\Controllers\API\Promoters;

use App\Helpers\WorkShiftPdf;
use App\Http\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\Tariff;
use App\Models\Dictionaries\WorkShiftStatus;
use App\Models\Partner\Partner;
use App\Models\User\Helpers\Currents;
use App\Models\WorkShift\WorkShift;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkShiftController extends Controller
{
    public function open(Request $request)
    {
        $current = Currents::get($request);
        if (!$current->terminalId()) {
            return APIResponse::error('Доступ запрещён');
        }

        $promoter = Partner::find($request->input('promoterId'));

        $tariff = $promoter->tariff()->first();

        if (!$tariff) {
            return APIResponse::error('Для промоутера не задан тариф');
        }

        $workShift = $promoter->getOpenedShift();
        if (!$workShift) {
            $workShift = WorkShift::create([
                'partner_id' => $promoter->id,
                'tariff_id' => $tariff->id,
                'terminal_id' => $current->terminalId(),
                'start_at' => now(),
                'status_id' => WorkShiftStatus::active,
                'balance' => $promoter->getLastShift()?->balance,
            ]);
        }

        return APIResponse::success('Смена открыта', ['start_at' => Carbon::parse($workShift->start_at)->format('Y-m-d H:i:s')]);
    }

    public function openSelfPay(Request $request)
    {
        $current = Currents::get($request);

        $currentDateTime = Carbon::now();
        $noonToday = Carbon::today()->setHour(11);

        $promoter = $current->partner();

        $tariff = ($currentDateTime->lessThan($noonToday) && $promoter->profile->auto_change_tariff == 1) ? Tariff::find(1) : $promoter->tariff()->first();

        if (!$tariff) {
            return APIResponse::error('Не задан тариф');
        }

        $workShift = $promoter->getOpenedShift();
        if (!$workShift) {
            $workShift = WorkShift::create([
                'partner_id' => $promoter->id,
                'tariff_id' => $tariff->id,
                'terminal_id' => 1,
                'start_at' => now(),
                'status_id' => WorkShiftStatus::active,
                'balance' => $promoter->getLastShift()?->balance,
            ]);
        }

        return APIResponse::success('Смена открыта', ['start_at' => Carbon::parse($workShift->start_at)->format('Y-m-d H:i:s')]);
    }

    public function pay(Request $request)
    {
        $workShift = WorkShift::query()
            ->with('tariff')
            ->where('partner_id', $request->input('partnerId'))
            ->whereNull('end_at')
            ->first();

        if (!$workShift) {
            return APIResponse::error('Смена не найдена');
        }

        $current = Currents::get($request);

        if ($current->terminalId() && $current->terminalId() !== $workShift->terminal_id)
        {
            $addr = $workShift->terminal->pier->info->address;
            return APIResponse::error("Смена была открыта на другой кассе: № $workShift->terminal_id ($addr)");
        }

        if ($request->input('payTaxi') > 500 || (($workShift->taxi + $request->input('payTaxi')) > 500))  {
            return APIResponse::error('Оплата такси не должна превышать 500 руб. за смену');
        }

        $sumToPay = $request->input('sumToPay');

        $workShift->paid_out = $workShift->paid_out + $sumToPay;

        $workShift->taxi = $workShift->taxi + $request->input('payTaxi');

        $workShift->save();

        return APIResponse::success('Выплата записана.');
    }

    public function close(Request $request)
    {
        $workShift = WorkShift::query()
            ->where('partner_id', $request->partnerId)
            ->whereNull('end_at')->first();

        if (!$workShift) {
            return APIResponse::error('Смена не найдена');
        }

        $partner = Partner::find($request->partnerId);

        $hasUnclosedOrders = $partner->orders()->where('status_id', OrderStatus::terminal_finishing)->exists();

        if ($hasUnclosedOrders){
            return APIResponse::error('На кассе есть не закрытый заказ.');
        }

        $current = Currents::get($request);

        if ($current->terminalId() && $current->terminalId() !== $workShift->terminal_id)
        {
            $addr = $workShift->terminal->pier->info->address;
            return APIResponse::error("Смена была открыта на другой кассе: № $workShift->terminal_id ($addr)");
        }

        $workShift->pay_for_time = $workShift->getPayForTime();
        $workShift->pay_for_out = $workShift->tariff->pay_for_out;
        $workShift->pay_total = $workShift->getShiftTotalPay();
        $workShift->end_at = now();
        $workShift->balance = ($workShift->pay_total + $workShift->balance) - $workShift->paid_out;
        $workShift->save();

        return APIResponse::success('Смена закрыта');
    }

    public function print(Request $request): JsonResponse
    {
        $workShift = WorkShift::query()
            ->with(['promoter', 'tariff'])
            ->where('partner_id', $request->input('partnerId'))
            ->whereNull('end_at')
            ->first();

        if ($workShift === null) {
            return APIResponse::error('Смена не найдена');
        }

        $pdf = WorkShiftPdf::print($workShift);

        return APIResponse::response([
            'workShift' => base64_encode($pdf),
            'file_name' => "Расписка N$workShift->id.pdf",
        ]);
    }

    public function changeCommissions(Request $request)
    {
        $current = Currents::get($request);

        $promoters = Partner::whereIn('id', $request->input('promotersIds'))->with(['workShifts', 'workShifts.tariff'])->get();

        foreach ($promoters as $promoter) {
            $openedShift = $promoter->getOpenedShift();
            if (!$openedShift){
                return APIResponse::error('У промоутера нет открытой смены');
            }
            $openedShift->commission_delta = $request->input('newCommValue') - $openedShift->tariff->commission;
            $openedShift->save();
        }
        return APIResponse::success('Ставка комиссии изменена');
    }
}
