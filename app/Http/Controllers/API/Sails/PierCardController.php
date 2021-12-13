<?php

namespace App\Http\Controllers\API\Sails;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Sails\Pier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PierCardController extends ApiController
{
    public function get(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null ||
            null === ($pier = Pier::query()->with(['status'])->where('id', $id)->first())) {
            return APIResponse::notFound();
        }

        /** @var Pier $pier */

        // fill data
        $values = [
            'name' => $pier->name,
            'status' => $pier->status->name,
        ];

        // send response
        return APIResponse::response($values);
    }
}
