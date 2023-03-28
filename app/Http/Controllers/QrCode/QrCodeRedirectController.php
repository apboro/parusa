<?php

namespace App\Http\Controllers\QrCode;

use App\Helpers\StatisticQrCodes;
use App\Http\Controllers\Controller;
use App\Models\QrCode;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class QrCodeRedirectController extends Controller
{
    public function redirect(string $hash): Application|RedirectResponse|Redirector
    {
        $existingCookieHash = request()->cookie('qrCodeHash');
        if ($existingCookieHash && !env('REGENERATE_COOKIE')) {
            $hash = $existingCookieHash;
        }

        /**@var \App\Models\QrCode $qrCode */
        $qrCode = QrCode::where('hash', $hash)->first();
        $link = $qrCode->link . '?ap_showcase_persons=&ap_showcase_date='
            . Carbon::now()->format('Y-m-d') . '&partner=' . $qrCode->partner->id;
        if (!$existingCookieHash) {
            StatisticQrCodes::addVisit($qrCode);
        }

        return redirect($link)->withCookie(cookie('qrCodeHash', $hash, 30240));
    }
}
