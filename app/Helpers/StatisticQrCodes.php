<?php

namespace App\Helpers;

use App\Models\QrCode;
use App\Models\QrCodesStatistic;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class StatisticQrCodes
{
    public static function addVisit(QrCode $qrCode)
    {
        QrCodesStatistic::create([
            'qr_code_id' => $qrCode->id,
            'is_visit' => true,
            'created_at' => Carbon::now()
        ]);
    }

    public static function addPayment(string $qrCodeHash)
    {
        try {
            $qrCode = QrCode::where('hash', $qrCodeHash)->first();
            Log::channel('qr-codes')->info('addPayment in statistic, $qrCode', [$qrCode]);
            QrCodesStatistic::create([
                'qr_code_id' => $qrCode->id,
                'is_payment' => true,
                'created_at' => Carbon::now()
            ]);
        } catch (\Exception $e){
            Log::channel('single')->error($e);
        }
    }

}
