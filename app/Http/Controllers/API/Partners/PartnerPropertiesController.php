<?php

namespace App\Http\Controllers\API\Partners;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Partner\Partner;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerPropertiesController extends ApiController
{
    /**
     * Update trip status.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function properties(Request $request): JsonResponse
    {
        $id = $request->input('id');

        /** @var Partner $partner */
        if ($id === null || null === ($partner = Partner::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Компания не найдена');
        }

        $name = $request->input('data.name');
        $value = $request->input('data.value');

        if (!$name || $value === null || !in_array($name, ['status_id', 'tickets_for_guides', 'can_reserve_tickets'])) {
            return APIResponse::error('Неверно заданы параметры');
        }

        try {
            switch ($name) {
                case 'status_id':
                    $partner->setStatus((int)$value);
                    break;
                case 'tickets_for_guides':
                    $partner->profile->tickets_for_guides = (int)$value;
                    $partner->profile->save();
                    break;
                case 'can_reserve_tickets':
                    $partner->profile->can_reserve_tickets = (bool)$value;
                    $partner->profile->save();
                    break;
            }
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success("Данные компании \"$partner->name\" обновлены", [
            'id' => $partner->id,
            'status' => $partner->status->name,
            'status_id' => $partner->status_id,
            'active' => $partner->hasStatus(PartnerStatus::active),
            'tickets_for_guides' => $partner->profile->tickets_for_guides,
            'can_reserve_tickets' => $partner->profile->can_reserve_tickets ? 1 : 0,
        ]);
    }
}
