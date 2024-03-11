<?php

namespace App\Http\Controllers\API\Promoters;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Common\File;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Hit\Hit;
use App\Models\Order\Order;
use App\Models\Partner\Partner;
use App\Models\Positions\Position;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoterViewController extends ApiController
{
    public function get(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $id = $request->input('id');

        if ($id === null ||
            null === ($partner = Partner::query()
                ->with([
                    'status', 'type', 'account', 'profile', 'files',
                    'positions', 'positions.info', 'positions.user', 'positions.user.profile',
                ])
                ->where('id', $id)->first())
        ) {
            return APIResponse::notFound('Промоутер не найден');
        }

        /** @var Partner $partner */
        $promoterUser = $partner->positions()->first()->user;
        $promoterUserProfile = $partner->positions()->first()->user->profile;

        $openShift = $partner->getOpenedShift();
        if ($openShift) {
            if ($openShift->tariff->pay_per_hour) {
                $interval = Carbon::parse($openShift->start_at)->diff(now());
                $payForTime = round(($interval->days * 24 + $interval->h + $interval->i / 60), 1) * $openShift->tariff->pay_per_hour;
            }
        }

        // fill data
        $values = [
            'id' => $partner->id,
            'representativeId' => $promoterUser->id,
            'name' => $partner->name,
            'created_at' => $partner->created_at->format('d.m.Y'),
            'notes' => $promoterUserProfile->notes,
            'phone' => $promoterUserProfile->mobile_phone,
            'email' => $promoterUserProfile->email,
            'has_access' => !empty($promoterUser->login) && !empty($promoterUser->password),
            'can_send_sms' => $partner->profile->can_send_sms ? 1 : 0,
            'login' => $promoterUser->login,
            'full_name' => $promoterUserProfile->fullName,
            'open_shift' => $partner->getOpenedShift(),
            'payForTime' => isset($payForTime) ? round($payForTime) : null,
            'payForOut' => $openShift->tariff->pay_for_out ?? null,
            'payCommission' => $openShift->pay_commission ?? null, //записывается в методе Ticket->payCommission()
            'paid_out' => $openShift->paid_out ?? null,
            'balance' => $partner->getLastShift()?->balance ?? null,
            'promoter_commission_rate' => $partner->tariff()->first()?->commission
        ];

        // send response
        return APIResponse::response($values);
    }
}
