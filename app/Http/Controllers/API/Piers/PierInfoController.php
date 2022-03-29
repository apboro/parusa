<?php

namespace App\Http\Controllers\API\Piers;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Common\Image;
use App\Models\Dictionaries\PiersStatus;
use App\Models\Piers\Pier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PierInfoController extends ApiController
{
    public function info(Request $request): JsonResponse
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
        ];

        // send response
        return APIResponse::response($values);
    }
}
