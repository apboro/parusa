<?php

namespace App\Http\Controllers\QrCode;

use App\Http\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\HitSource;
use App\Models\Hit\Hit;
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
        Hit::register($current->isStaff() ? HitSource::admin : HitSource::partner);

        $link = $data['link'] ?? 'https://city-tours-spb.ru/';
        $partnerId = $current->isStaff() ? $data['partner_id'] : $current->partnerId();

        $qrCode = QrCode::firstOrCreate(['id' => $request->id],
            [
                'partner_id' => $partnerId,
            ]);

        $qrCode->name = $data['name'];
        $qrCode->link = $link;
        $qrCode->save();

    }
}
