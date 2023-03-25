<?php

namespace App\Http\Controllers\QrCode;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use App\Models\QrCodesStatistic;

class QrCodesStatisticController extends Controller
{
    public static function addVisit(QrCode $qrCode)
    {
        QrCodesStatistic::create([
            'qr_code_id' => $qrCode->id,
            'partner_id' => $qrCode->partner_id,
            'is_visit' => true
        ]);
    }

    public static function addPayment(string $qrCodeHash)
    {
        $qrCode = QrCode::where('hash', $qrCodeHash)->first();
        QrCodesStatistic::create([
            'qr_code_id' => $qrCode->id,
            'partner_id' => $qrCode->partner_id,
            'is_payment' => true
        ]);
    }
}
