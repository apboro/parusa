<?php

namespace App\Http\Controllers\QrCode;

use App\Http\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\QrCode;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QrCodeGenerateController extends Controller
{
    public function generateQr(Request $request): JsonResponse
    {
        $link = QrCode::makeLinkForQrCode($request->id);

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
            'link' => $link
        ]);
    }

}
