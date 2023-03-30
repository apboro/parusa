<?php

namespace App\Http\Controllers\QrCode;

use App\Helpers\StatisticQrCodes;
use App\Http\Controllers\Controller;
use App\Models\QrCode;
use Illuminate\Http\Request;

class QrCodeRedirectController extends Controller
{
    public function redirect(string $hash, Request $request)
    {
        /**@var QrCode|null $qrCode */
        $qrCode = QrCode::query()->where('hash', $hash)->first();

        if ($qrCode === null) {
            $link = env('QR_FALLBACK_LINK');

            return redirect($link);
        }

        $link = $qrCode->link;

        StatisticQrCodes::addVisit($qrCode);

        return redirect($link)->withCookie(cookie('qrCodeHash', $hash, env('QR_LIFETIME', 30240)));
    }
}
