<?php

namespace App\Http\Controllers\QrCode;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\APIListRequest;
use App\Models\QrCode;
use App\Models\User\Helpers\Currents;
use Illuminate\Http\JsonResponse;


class QrCodeListController extends Controller
{
    protected string $rememberKey = CookieKeys::qr_codes_list;

    public function getQrCodesList(ApiListRequest  $request): JsonResponse
    {
        $current = Currents::get($request);
        $qrcodes = QrCode::query()->where('partner_id', $current->partnerId())
            ->withCount([
                'statisticVisit',
                'statisticPayment',
            ])
            ->paginate($request->perPage(10));

        return APIResponse::list(
            $qrcodes,
            ['Название', 'Ссылка', 'Визиты', 'Покупки', 'QR-код']
        );
    }

}
