<?php

namespace App\Http\Controllers\API\Promoters;

use App\Helpers\WorkShiftPdf;
use App\Http\APIResponse;
use App\Http\Controllers\Controller;
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

        $this->save($workShift);

        return APIResponse::success('Выплата записана.');
    }

    public function close(Request $request)
    {
        $workShift = WorkShift::query()
            ->where('partner_id', $request->partnerId)
            ->whereNull('end_at')->first();
        $this->save($workShift, true);

        return APIResponse::success('Смена закрыта');
    }

    public function save(WorkShift $workShift, bool $close = false)
    {
        $workShift->pay_for_time = $workShift->getPayForTime();
        $workShift->pay_for_out = $workShift->tariff->pay_for_out;
        $workShift->pay_total = $workShift->getShiftTotalPay();

        if ($close){
            $workShift->end_at = now();
        }

        $workShift->save();
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
            $openedShift->commission_delta = $request->input('newCommValue') - $openedShift->tariff->commission;
            $openedShift->save();
        }
        return APIResponse::success('Ставка комиссии изменена');
    }
}
