<?php

namespace App\Http\Controllers\API\Sails;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Common\Image;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Sails\Excursion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExcursionCardController extends ApiController
{
    public function get(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null ||
            null === ($excursion = Excursion::query()->with(['status', 'programs'])->where('id', $id)->first())) {
            return APIResponse::notFound('Экскурсия не найдена');
        }

        /** @var Excursion $excursion */

        // fill data
        $values = [
            'name' => $excursion->name,
            'status' => $excursion->status->name,
            'status_id' => $excursion->status_id,
            'active' => $excursion->hasStatus(ExcursionStatus::active),
            'images' => $excursion->images->map(function (Image $image) {
                return $image->url;
            }),
            'programs' => $excursion->programs->map(function (ExcursionProgram $program) {
                return $program->name;
            }),
            'duration' => $excursion->info->duration,
            'description' => $excursion->info->description,
            'announce' => $excursion->info->announce,
        ];

        // send response
        return APIResponse::response($values);
    }
}
