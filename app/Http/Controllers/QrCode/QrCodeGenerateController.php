<?php

namespace App\Http\Controllers\QrCode;

use App\Http\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\HitSource;
use App\Models\Hit\Hit;
use App\Models\Order\Order;
use App\Models\QrCodes\QrCode;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class QrCodeGenerateController extends Controller
{
    public function generateQr(Request $request): JsonResponse
    {
        Hit::register(HitSource::crm);
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

    /**
     * @throws \JsonException
     */
    public function generateOrderPaymentQr(Request $request)
    {
        Hit::register(HitSource::crm);
        $order = Order::findOrFail($request->input('orderId'));
        $orderSecret = json_encode([
            'id' => $order->id,
            'ts' => Carbon::now(),
            'ip' => $request->ip(),
            'ref' => null,
        ], JSON_THROW_ON_ERROR);

        $secret = Crypt::encrypt($orderSecret);

        $link = config('showcase.showcase_payment_page') . '?order=' . $secret;

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

}
