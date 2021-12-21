<?php

namespace App\Http\Controllers\API\Partners;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Partner\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerCardController extends ApiController
{
    public function get(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null ||
            null === ($partner = Partner::query()->with(['status'])->where('id', $id)->first())) {
            return APIResponse::notFound();
        }

        /** @var Partner $partner */

        // fill data
        $values = [
            'name' => $partner->name,
            'status' => $partner->status->name,
            'status_id' => $partner->status_id,
        ];

        // send response
        return APIResponse::response($values);
    }
}
