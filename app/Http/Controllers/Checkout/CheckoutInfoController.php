<?php

namespace App\Http\Controllers\Checkout;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Common\Image;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Dictionaries\PiersStatus;
use App\Models\Excursions\Excursion;
use App\Models\Piers\Pier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CheckoutInfoController extends ApiController
{
    /**
     * Get excursion info.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function excursion(Request $request): JsonResponse
    {
        $id = $request->input('id');

        /** @var Excursion $excursion */
        if ($id === null ||
            null === ($excursion = Excursion::query()->with(['status', 'programs'])->where('id', $id)->first())) {
            return APIResponse::notFound('Экскурсия не найдена');
        }

        // fill data
        $values = [
            'name' => $excursion->name,
            'status' => $excursion->status->name,
            'active' => $excursion->hasStatus(ExcursionStatus::active),
            'images' => $excursion->images->map(function (Image $image) {
                return 'data:' . $image->mime . ';base64, ' . base64_encode(Storage::disk($image->disk)->get($image->filename));
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

    /**
     * Get pier info.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function pier(Request $request): JsonResponse
    {
        $id = $request->input('id');

        /** @var Pier $pier */
        if ($id === null ||
            null === ($pier = Pier::query()->with(['status', 'info', 'images'])->where('id', $id)->first())) {
            return APIResponse::notFound('Причал не найден');
        }

        // fill data
        $values = [
            'name' => $pier->name,
            'address' => $pier->info->address,
            'work_time' => $pier->info->work_time,
            'phone' => $pier->info->phone,
            'latitude' => $pier->info->latitude,
            'longitude' => $pier->info->longitude,
            'status' => $pier->status->name,
            'active' => $pier->hasStatus(PiersStatus::active),
            'description' => $pier->info->description,
            'way_to' => $pier->info->way_to,
            'images' => $pier->images->map(function (Image $image) {
                return 'data:' . $image->mime . ';base64, ' . base64_encode(Storage::disk($image->disk)->get($image->filename));
            }),
            'map_images' => $pier->mapImages->map(function (Image $image) {
                return 'data:' . $image->mime . ';base64, ' . base64_encode(Storage::disk($image->disk)->get($image->filename));
            }),
        ];

        // send response
        return APIResponse::response($values);
    }
}
