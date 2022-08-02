<?php

namespace App\Http\Controllers\API\Excursions;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Excursions\Excursion;
use App\Models\Tickets\TicketsRatesList;
use App\Models\User\Helpers\Currents;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExcursionPartnerShowcaseVisibilityController extends ApiController
{
    /**
     * Change partner showcase excursion visibility.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function visibility(Request $request): JsonResponse
    {
        $id = $request->input('excursion_id');

        if ($id === null || null === ($excursion = Excursion::query()->where('id', $id)->first())) {
            return APIResponse::notFound('Экскурсия не найдена');
        }
        /** @var Excursion $excursion */

        $current = Currents::get($request);
        $visibility = $request->input('visible', true);

        if ($visibility) {
            $excursion->partnerShowcaseHide()->detach($current->partnerId());
        } else {
            $excursion->partnerShowcaseHide()->attach($current->partnerId());
        }

        return APIResponse::success("Видимость экскурсии \"$excursion->name\" обновлена");
    }
}
