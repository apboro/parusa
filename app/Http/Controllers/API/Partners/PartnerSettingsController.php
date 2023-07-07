<?php

namespace App\Http\Controllers\API\Partners;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Dictionaries\HitSource;
use App\Models\Excursions\Excursion;
use App\Models\Hit\Hit;
use App\Models\User\Helpers\Currents;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerSettingsController extends ApiController
{
    /**
     * Get partner settings to display.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function settings(Request $request): JsonResponse
    {
        Hit::register(HitSource::partner);
        $current = Currents::get($request);
        $showcaseUrl = config('app.showcase_ap_page');

        $code = "<!-- Загрузка скрипта -->\n";
        $code .= "<script src=\"" . env('APP_URL') . "/js/showcase.js\"></script>\n";
        $code .= "<!-- Настройки -->\n";
        $code .= "<script id=\"ap-showcase-config\" type=\"application/json\">{\"partner\": " . $current->partnerId() . "}</script>";
        $code .= "\n<!-- Вставить в то место, где нужно разместить приложение -->\n";
        $code .= "<div id=\"ap-showcase\"></div>";

        return APIResponse::response([
            'partner_id' => $current->partnerId(),
            'old_link' => $this->appLink($showcaseUrl, $current->partnerId()),
            'link' => $this->referralShowcaseLink($current->partnerId()),
            'referral_link' => $this->referralLink($current->partnerId()),
            'code' => $code,
            'qr_target_page' => null,
        ]);
    }

    /**
     * Get partner widget 2 to display.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function widget(Request $request): JsonResponse
    {
        Hit::register(HitSource::partner);
        $current = Currents::get($request);

        $excursions = Excursion::query()
            ->where('status_id', ExcursionStatus::active)
            ->where('only_site', false)
            ->orderBy('name')
            ->get();

        $excursionsIDs = $request->input('excursions');
        if (!empty($excursionsIDs)) {
            asort($excursionsIDs);
            $codeExcursions = '"excursions":[' . implode(',', $excursionsIDs) . ']';
        }

        $code = "<!-- Загрузка скрипта -->\n";
        $code .= "<script src=\"" . env('APP_URL') . "/js/showcase.js\"></script>\n";
        $code .= "<!-- Настройки -->\n";
        $code .= "<script id=\"ap-showcase-config\" type=\"application/json\">{" .
            implode(
                ', ',
                array_filter([
                    '"partner":' . $current->partnerId(),
                    $codeExcursions ?? null,
                ])
            )
            . "}</script>";
        $code .= "\n<!-- Вставить в то место, где нужно разместить приложение -->\n";
        $code .= "<div id=\"ap-showcase\"></div>";

        $code2 = "<!-- Загрузка скрипта -->\n";
        $code2 .= "<script src=\"" . env('APP_URL') . "/js/showcase2.js\"></script>\n";
        $code2 .= "<!-- Настройки -->\n";
        $code2 .= "<script id=\"ap-showcase-config\" type=\"application/json\">{" .
            implode(
                ', ',
                array_filter([
                    '"partner":' . $current->partnerId(),
                    $codeExcursions ?? null,
                ])
            )
            . "}</script>";
        $code2 .= "\n<!-- Вставить в то место, где нужно разместить приложение -->\n";
        $code2 .= "<div id=\"ap-showcase2\"></div>";

        return APIResponse::response([
            'code' => $code,
            'code2' => $code2,
            'excursions' => $excursions->map(function (Excursion $excursion) {
                return ['id' => $excursion->id, 'name' => $excursion->name];
            }),
        ]);
    }

    /**
     * Generate QR code.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function qr(Request $request): JsonResponse
    {
        Hit::register(HitSource::partner);
        $url = $request->input('url');
        if (empty($url)) {
            return APIResponse::error('URL не задан');
        }
        $current = Currents::get($request);
        $link = $this->appLink($url, $current->partnerId()) . '&media=qr';

        // generate QR
        $result = Builder::create()
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->size(600)
            ->margin(10)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->data($link)
            ->build();

        return APIResponse::response([
            'qr' => $result->getDataUri(),
        ]);
    }

    /**
     * Make app link.
     *
     * @param string $url
     * @param int $partnerId
     *
     * @return  string
     */
    protected function appLink(string $url, int $partnerId): string
    {
        $parts = explode('?', $url);
        $url = $parts[0];

        if (isset($parts[1])) {
            $url .= '?' . $parts[1] . '&';
        } else {
            $url .= '?';
        }
        $url .= 'partner=' . $partnerId;

        return $url;
    }

    /**
     * Make referral link link.
     *
     * @param int $partnerId
     *
     * @return  string
     */
    protected function referralLink(int $partnerId): string
    {
        return route('referral_link', ['id' => $partnerId]);
    }

    /**
     * Make referral link link.
     *
     * @param int $partnerId
     *
     * @return  string
     */
    protected function referralShowcaseLink(int $partnerId): string
    {
        return route('referral_link', ['id' => $partnerId, 'showcase']);
    }
}
