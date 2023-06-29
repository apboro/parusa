<?php

namespace App\Http\Controllers\QrCode;

use App\Helpers\StatisticQrCodes;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\HitSource;
use App\Models\Hit\Hit;
use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QrCodeRedirectController extends Controller
{
    public function redirect(string $hash, Request $request)
    {
        Hit::register(HitSource::qrlink);
        /**@var QrCode|null $qrCode */
        $qrCode = QrCode::where('hash', $hash)->first();

        if ($qrCode === null) {
            $link = env('QR_FALLBACK_LINK');

            return redirect($link);
        }

        $link = $qrCode->link;

        StatisticQrCodes::addVisit($qrCode);
        $cookie = cookie('qrCodeHash', $hash, env('QR_LIFETIME', 30240),
            null, '', true, true, false,'None');

        return response()
            ->view('redirect', ['link'=>$link])
            ->withoutCookie('referralLink')
            ->withCookie($cookie);
    }
}
