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
                    'positions', 'positions.info', 'positions.accessStatus', 'positions.user', 'positions.user.profile',
                ])
                ->where('id', $id)->first())
        ) {
            return APIResponse::notFound('Партнёр не найден');
        }

        /** @var Partner $partner */
        $profile = $partner->profile;

        // fill data
        $values = [
            'name' => $partner->name,
            'created_at' => $partner->created_at->format('d.m.Y'),
            'status' => $partner->status->name,
            'status_id' => $partner->status_id,
            'active' => $partner->hasStatus(PartnerStatus::active),
            'type' => $partner->type->name,
            'tickets_for_guides' => $profile->tickets_for_guides ?? 0,
            'can_reserve_tickets' => $profile->can_reserve_tickets ? 1 : 0,
            'can_send_sms' => $profile->can_send_sms ? 1 : 0,
            'notes' => $profile->notes,
            'documents' => $partner->files->map(function (File $file) {
                return ['id' => $file->id, 'name' => $file->original_filename, 'url' => $file->url, 'type' => $file->mime];
            }),
            'positions' => $partner->positions->map(function (Position $position) {
                return [
                    'position_id' => $position->id,
                    'user_id' => $position->user->id,
                    'user' => $position->user->profile->fullName,
                    'title' => $position->title,
                    'work_phone' => $position->info->work_phone,
                    'work_phone_additional' => $position->info->work_phone_additional,
                    'email' => $position->info->email,
                    'status' => $position->accessStatus->name,
                    'status_id' => $position->access_status_id,
                    'active' => $position->hasStatus(PositionAccessStatus::active, 'access_status_id'),
                ];
            }),
        ];

        // send response
        return APIResponse::response($values);
    }
}
