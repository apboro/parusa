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

class QrCodeCreateController extends Controller
{

    public function updateOrCreate(Request $request)
    {
        $data = $request->data;

        $current = Currents::get($request);

        $link = $data['link'] ?? 'https://city-tours-spb.ru/podbor-reysa/';

        $qrCode = QrCode::firstOrCreate(['id' => $request->id],
            [
                'partner_id' =>$current->partnerId(),
            ]);

        $qrCode->name = $data['name'];
        $qrCode->link = $link;
        $qrCode->save();

    }
}
