<?php

namespace App\Http\Controllers\API\Piers;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Common\Image;
use App\Models\Dictionaries\PiersStatus;
use App\Models\Sails\Pier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PierViewController extends ApiController
{
    public function view(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null ||
            null === ($pier = Pier::query()->with(['status', 'info', 'images'])->where('id', $id)->first())) {
            return APIResponse::notFound('Причал не найден');
        }

        /** @var Pier $pier */

        // fill data
        $values = [
            'name' => $pier->name,
            'address' => $pier->info->address,
            'work_time' => $pier->info->work_time,
            'phone' => $pier->info->phone,
            'latitude' => $pier->info->latitude,
            'longitude' => $pier->info->longitude,
            'status' => $pier->status->name,
            'status_id' => $pier->status_id,
            'active' => $pier->hasStatus(PiersStatus::active),
            'description' => $pier->info->description,
            'way_to' => $pier->info->way_to,
            'images' => $pier->images->map(function (Image $image) {
                return $image->url;
            })
        ];

        // send response
        return APIResponse::response($values);
    }
}
