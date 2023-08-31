<?php

namespace App\Http\Controllers\API\Excursions;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Common\Image;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Dictionaries\HitSource;
use App\Models\Excursions\Excursion;
use App\Models\Hit\Hit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExcursionViewController extends ApiController
{
    public function view(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $id = $request->input('id');

        if ($id === null ||
            null === ($excursion = Excursion::query()->with(['status', 'programs', 'images', 'tripImages'])->where('id', $id)->first())) {
            return APIResponse::notFound('Экскурсия не найдена');
        }

        /** @var Excursion $excursion */

        // fill data
        $values = [
            'name' => $excursion->name,
            'name_receipt' => $excursion->name_receipt,
            'status' => $excursion->status->name,
            'status_id' => $excursion->status_id,
            'active' => $excursion->hasStatus(ExcursionStatus::active),
            'images' => $excursion->images->map(function (Image $image) {
                return $image->url;
            }),
            'trip_images' => $excursion->tripImages->map(function (Image $image) {
                return $image->url;
            }),
            'programs' => $excursion->programs->map(function (ExcursionProgram $program) {
                return $program->name;
            }),
            'duration' => $excursion->info->duration,
            'description' => $excursion->info->description,
            'announce' => $excursion->info->announce,
            'only_site' => $excursion->only_site,
            'is_single_ticket' => $excursion->is_single_ticket,
            'reverse_excursion' => $excursion->reverseExcursion?->name,
            'excursion_type' => $excursion->type?->name ?? "Нет"
        ];

        // send response
        return APIResponse::response($values);
    }
}
