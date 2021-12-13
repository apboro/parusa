<?php

namespace App\Http\Controllers\API\Sails;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
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
            return APIResponse::notFound();
        }

        /** @var Excursion $excursion */

        // fill data
        $values = [
            'name' => $excursion->name,
            'status' => $excursion->status->name,
        ];

        // send response
        return APIResponse::response($values);
    }
}
