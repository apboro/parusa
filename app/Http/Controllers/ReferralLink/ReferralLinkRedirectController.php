<?php

namespace App\Http\Controllers\ReferralLink;

use App\Http\Controllers\Controller;
use App\Models\Partner\Partner;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReferralLinkRedirectController extends Controller
{
    public function redirect(int $id, Request $request): Response
    {
        /**@var Partner|null $partner */
        $partner = Partner::query()->where('id', $id)->first();

        if ($request->has('showcase')) {
            $showcaseUrl = config('app.showcase_ap_page');
            $parts = explode('?', $showcaseUrl);
            $link = $parts[0];
            if (isset($parts[1])) {
                $link .= '?' . $parts[1] . '&';
            } else {
                $link .= '?';
            }
            $link .= 'partner=' . $id;
        } else {
            $link = env('REFERRAL_LINK_TARGET', 'https://city-tours-spb.ru/');
        }

        $cookie = cookie(
            'referralLink',
            $partner === null ? null : $partner->id,
            env('REFERRAL_LINK_LIFETIME', 30240),
            null,
            '',
            true,
            true,
            false,
            'None'
        );

        return response()
            ->view('redirect', ['link' => $link])
            ->withoutCookie('qrCodeHash')
            ->withCookie($cookie);
    }
}
