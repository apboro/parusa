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
use App\Models\User\Helpers\Currents;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExcursionInfoController extends ApiController
{
    public function info(Request $request): JsonResponse
    {
        $current = Currents::get($request);

        if ($current->isRepresentative()) {
            Hit::register(HitSource::partner);
        } else if ($current->isStaffTerminal()) {
            Hit::register(HitSource::terminal);
        } else {
            Hit::register(HitSource::admin);
        }

        $id = $request->input('id');

        /** @var Excursion $excursion */
        if ($id === null ||
            null === ($excursion = Excursion::query()->with(['status', 'programs'])->where('id', $id)->first())) {
            return APIResponse::notFound('Экскурсия не найдена');
        }

        // fill data
        $values = [
            'id' => $excursion->id,
            'name' => $excursion->name,
            'status' => $excursion->status->name,
            'provider' => $excursion->provider->name,
            'active' => $excursion->hasStatus(ExcursionStatus::active),
            'images' => $excursion->images->map(function (Image $image) {
                try {
                    return 'data:' . $image->mime . ';base64, ' . base64_encode(Storage::disk($image->disk)->get($image->filename));
                } catch (FileNotFoundException $e){
                    return null;
                }
            }),
            'programs' => $excursion->programs->map(function (ExcursionProgram $program) {
                return $program->name;
            }),
            'duration' => $excursion->info->duration,
            'description' => $excursion->info->description,
            'announce' => $excursion->info->announce,
            'map_images' => $excursion->tripImages->map(function (Image $image) {
                try {
                    return 'data:' . $image->mime . ';base64, ' . base64_encode(Storage::disk($image->disk)->get($image->filename));
                } catch (FileNotFoundException $e) {
                    return null;
                }
            }),
        ];

        // send response
        return APIResponse::response($values,
            ['schedule_img' => config('app.url').'/storage/images/city_tour-schedule.png']);
    }
}
