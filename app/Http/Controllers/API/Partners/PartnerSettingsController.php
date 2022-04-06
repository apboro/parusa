<?php

namespace App\Http\Controllers\API\Partners;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
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
        $current = Currents::get($request);
        $showcaseUrl = env('SHOWCASE_AP_PAGE');

        $code = "<!-- Загрузка скрипта -->\n";
        $code .= "<script src=\"" . env('APP_URL') . "/js/showcase.js\"></script>\n";
        $code .= "<!-- Настройки -->\n";
        $code .= "<script id=\"ap-showcase-config\" type=\"application/json\">{\"partner\": " . $current->partnerId() . "}</script>";
        $code .= "\n<!-- Вставить в то место, где нужно разместить приложение -->\n";
        $code .= "<div id=\"ap-showcase\"></div>";

        return APIResponse::response([
            'partner_id' => $current->partnerId(),
            'link' => $this->appLink($showcaseUrl, $current->partnerId()),
            'code' => $code,
            'qr_target_page' => null,
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
}
