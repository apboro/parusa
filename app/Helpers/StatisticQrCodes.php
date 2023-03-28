<?php

namespace App\Helpers;

use App\Models\QrCode;
use App\Models\QrCodesStatistic;
use Carbon\Carbon;

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
        $qrCode = QrCode::where('hash', $qrCodeHash)->first();
        QrCodesStatistic::create([
            'qr_code_id' => $qrCode->id,
            'is_payment' => true,
            'created_at' => Carbon::now()
        ]);
    }
}
