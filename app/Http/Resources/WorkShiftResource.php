<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\WorkShift\WorkShift */
class WorkShiftResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'terminal_id' => $this->terminal_id,
            'start_at' => $this->start_at->translatedFormat('D, d M Y H:i'),
            'end_at' => $this->end_at,
            'status_id' => $this->status_id,
            'pay_for_time' => $this->pay_for_time,
            'pay_for_out' => $this->pay_for_out,
            'sales_total' => $this->sales_total,
            'pay_commission' => $this->pay_commission,
            'pay_total' => $this->pay_total,
            'paid_out' => $this->paid_out,
            'balance' => $this->balance,
            'commission_delta' => $this->commission_delta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'taxi' => $this->taxi,
            'promoter' => $this->promoter,
            'tariff_id' => $this->tariff_id,
            'partner_id' => $this->partner_id,
            'tariff' => $this->tariff,
            'address' => $this->terminal->pier->info->address
        ];
    }
}
