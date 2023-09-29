<?php

namespace App\Http\Controllers;

use App\Http\APIResponse;
use App\Models\Dictionaries\WorkShiftStatus;
use App\Models\WorkShift;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WorkShiftController extends Controller
{
    public function save(Request $request)
    {
        $workShift = WorkShift::create([
            'partner_id' => $request->promoterId,
            'tariff_id' => $request->data['tariff_id'],
            'start_at' => now(),
            'status_id' => WorkShiftStatus::active,
        ]);

        return APIResponse::success('Смена открыта', ['start_at' => Carbon::parse($workShift->start_at)->format('Y-m-d H:i:s')]);
    }

    public function close(Request $request)
    {
        WorkShift::query()
            ->where('partner_id', $request->partnerId)
            ->whereNull('end_at')->update(['end_at' => now()]);

        return APIResponse::success('Смена закрыта');
    }
}
