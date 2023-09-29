<?php

namespace App\Http\Controllers\API\Promoters;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Common\File;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Hit\Hit;
use App\Models\Partner\Partner;
use App\Models\Positions\Position;
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
        $profile = $partner->profile;
        $promoterUser = $partner->positions()->first()->user;
        $promoterUserProfile = $partner->positions()->first()->user->profile;
        // fill data
        $values = [
            'id' => $partner->id,
            'representativeId' => $promoterUser->id,
            'name' => $partner->name,
            'created_at' => $partner->created_at->format('d.m.Y'),
            'notes' => $promoterUserProfile->notes,
            'phone' =>$promoterUserProfile->mobile_phone,
            'email' => $promoterUserProfile->email,
            'has_access' => !empty($promoterUser->login) && !empty($promoterUser->password),
            'login' => $promoterUser->login,
            'full_name' => $promoterUserProfile->fullName,
            'open_shift' => $partner->getOpenedShift(),
            ''
        ];

        // send response
        return APIResponse::response($values);
    }
}
